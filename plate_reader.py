# plate_reader.py
import cv2
import pytesseract
import os

def detect_plate(image_path):
    img = cv2.imread(image_path)

    # Convert to gray
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    
    # Use OCR to extract text
    plate_text = pytesseract.image_to_string(gray, config='--psm 8')  # for single-line text

    return plate_text.strip()
