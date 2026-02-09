from flask import Flask, render_template, request, redirect, url_for, session, flash
from werkzeug.utils import secure_filename
from werkzeug.security import generate_password_hash, check_password_hash
from flask_dance.contrib.google import make_google_blueprint, google
from flask_cors import CORS
from pymongo import MongoClient
from dotenv import load_dotenv
from datetime import datetime
import os

# Local module
from detection.detector import process_video, save_results

load_dotenv()
app = Flask(__name__)
app.secret_key = os.getenv("FLASK_SECRET_KEY")
CORS(app)
os.environ["OAUTHLIB_INSECURE_TRANSPORT"] = "1"

# MongoDB Setup
client = MongoClient(os.getenv("MONGO_URI"))
db = client["customerTrackingDB"]
admins_col = db["admins"]
vehicle_col = db["vehicle_logs"]

# Google OAuth
google_bp = make_google_blueprint(
    client_id=os.getenv("GOOGLE_OAUTH_CLIENT_ID"),
    client_secret=os.getenv("GOOGLE_OAUTH_CLIENT_SECRET"),
    scope=["openid", "https://www.googleapis.com/auth/userinfo.email", "https://www.googleapis.com/auth/userinfo.profile"],
    redirect_url="/google_login"
)
app.register_blueprint(google_bp, url_prefix="/login")

UPLOAD_FOLDER = "uploads"
STATIC_UPLOAD = os.path.join("static", "uploads")
os.makedirs(UPLOAD_FOLDER, exist_ok=True)
os.makedirs(STATIC_UPLOAD, exist_ok=True)

@app.route('/')
def index():
    return render_template('index.html')

@app.route('/admin_signup', methods=['GET', 'POST'])
def admin_signup():
    if request.method == 'POST':
        data = dict(request.form)
        if admins_col.find_one({'email': data['email']}):
            return "Email already exists. <a href='/admin_signup'>Try again</a>"
        data['password'] = generate_password_hash(data['password'])
        admins_col.insert_one(data)
        return redirect(url_for('admin_login'))
    return render_template('admin_signup.html')

@app.route('/admin_login', methods=['GET', 'POST'])
def admin_login():
    if request.method == 'POST':
        email = request.form['email']
        pwd = request.form['password']
        user = admins_col.find_one({'email': email})
        if user and check_password_hash(user['password'], pwd):
            session['username'] = user['username']
            return redirect(url_for('admin_home'))
        flash("Invalid credentials", "danger")
    return render_template('admin_login.html')

@app.route('/google_login')
def google_login():
    if not google.authorized:
        return redirect(url_for("google.login"))
    resp = google.get("/oauth2/v2/userinfo")
    email = resp.json().get("email")
    admin = admins_col.find_one({"email": email})
    if admin:
        session['username'] = admin['username']
        return redirect(url_for('admin_home'))
    return "Unauthorized Google account."

@app.route('/admin_forgot_password', methods=['GET', 'POST'])
def admin_forgot_password():
    msg, ok = "", False
    if request.method == 'POST':
        email = request.form['email']
        new_hash = generate_password_hash(request.form['new_password'])
        result = admins_col.update_one({'email': email}, {'$set': {'password': new_hash}})
        msg = "Password updated successfully." if result.matched_count else "Email not found."
        ok = bool(result.matched_count)
    return render_template('admin_forgot_password.html', message=msg, success=ok)

@app.route('/logout')
def logout():
    session.clear()
    return redirect(url_for('index'))

@app.route('/admin_home')
def admin_home():
    if 'username' not in session:
        return redirect(url_for('admin_login'))
    return render_template('admin_home.html', username=session['username'])

@app.route('/upload_page')
def upload_page():
    if 'username' not in session:
        return redirect(url_for('admin_login'))
    return render_template('upload_vehicle.html')

@app.route('/view_logs')
def view_logs():
    if 'username' not in session:
        return redirect(url_for('admin_login'))

    logs = list(vehicle_col.find())
    for log in logs:
        try:
            in_time = datetime.strptime(log.get('Inward'), '%Y-%m-%d %H:%M:%S')
            out_time = datetime.strptime(log.get('Outward'), '%Y-%m-%d %H:%M:%S')
            duration = out_time - in_time
            seconds = int(duration.total_seconds())
            h = seconds // 3600
            m = (seconds % 3600) // 60
            s = seconds % 60
            log['Duration'] = f"{h}h {m}m {s}s"
        except:
            log['Duration'] = "N/A"

    return render_template('view_logs.html', logs=logs)

@app.route('/upload_vehicle', methods=['POST'])
def upload_vehicle():
    if 'username' not in session:
        return redirect(url_for('admin_login'))

    file = request.files.get('video')
    if not file or file.filename == "":
        return "No file selected."

    filename = secure_filename(file.filename)
    save_path = os.path.join(UPLOAD_FOLDER, filename)
    file.save(save_path)

    static_path = os.path.join(STATIC_UPLOAD, filename)
    if not os.path.exists(static_path):
        os.rename(save_path, static_path)
    web_path = f"/static/uploads/{filename}"

    detections = process_video(static_path)
    if detections:
        out_file = f"output/{datetime.now().strftime('%Y%m%d_%H%M%S')}_log.xlsx"
        save_results(detections, out_file)
        vehicle_col.insert_many(detections)

    return render_template("result.html", video=web_path, detections=detections)

@app.route('/delete_log', methods=['POST'])
def delete_log():
    if 'username' not in session:
        return redirect(url_for('admin_login'))

    plate_number = request.form.get('plate_number')
    if plate_number:
        vehicle_col.delete_one({'License Plate': plate_number})
        flash('Log deleted successfully.', 'success')
    return redirect(url_for('view_logs'))

if __name__ == "__main__":
    app.run(debug=True, use_reloader=False)
