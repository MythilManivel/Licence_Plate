document.querySelector('.lookup-form').addEventListener('submit', function (e) {
    e.preventDefault();
    let licensePlate = document.querySelector('#license_plate').value;
    
    // Simulate AJAX request for demo purposes
    setTimeout(() => {
        const results = document.querySelector('#lookupResults');
        results.innerHTML = `<h2>Vehicle Details</h2>
                             <p>License Plate: ${licensePlate}</p>
                             <p>Owner Name: John Doe</p>
                             <p>Phone Number: 123-456-7890</p>
                             <p>Address: 1234 Elm Street, City</p>`;
    }, 1000);
});
