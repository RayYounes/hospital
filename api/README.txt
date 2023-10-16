I used WAMP64 with PHP for this project. To integrate Firebase JWT authentication, I downloaded the Composer package using the following command: "composer require firebase/php-jwt".

It's essential to ensure that the directory is correctly placed for testing purposes.

I conducted testing of each API using POSTMAN. Please follow the steps outlined below:

Begin by creating the first Admin using the "create-admin.php" API.
After creating the Admin, you can proceed to create doctors and patients.
Note: Admin privileges are mandatory for creating doctors and patients, as specified in the task.
Once both doctors and patients are created, you can proceed to test the remaining APIs. Ensure that the necessary validations, security measures, and error handling are in place.

Feel free to reach out via email at ray.younes46@gmail.com if you have any questions.



Below, you will find a description of the tables used to fulfill the project requirements.

"Database name : hospital"

CREATE TABLE Users (
    userID SERIAL PRIMARY KEY,
    username VARCHAR(190) UNIQUE NOT NULL,
    passwordHash VARCHAR(255) NOT NULL,
    userType ENUM('Doctor', 'Patient', 'Admin') NOT NULL,
    authToken TEXT,
    name VARCHAR(190) NOT NULL,  
    email VARCHAR(190) UNIQUE NOT NULL
);


CREATE TABLE Doctors (
    doctorID INT PRIMARY KEY REFERENCES Users(userID) ON DELETE CASCADE,
    specialization VARCHAR(255) NOT NULL,
    availability TEXT
);


CREATE TABLE Patients (
    patientID INT PRIMARY KEY REFERENCES Users(userID) ON DELETE CASCADE
);


CREATE TABLE DoctorPatientMap (
    doctorID INT REFERENCES Doctors(doctorID) ON DELETE CASCADE,
    patientID INT REFERENCES Patients(patientID) ON DELETE CASCADE,
    status INT,
    PRIMARY KEY (doctorID, patientID)
    
);


CREATE TABLE Files (
    fileID SERIAL PRIMARY KEY,
    doctorID INT REFERENCES Doctors(doctorID) ON DELETE CASCADE,
    patientID INT REFERENCES Patients(patientID) ON DELETE CASCADE,
    filePath TEXT NOT NULL,
    description TEXT
);


CREATE TABLE Appointments (
    appointmentID SERIAL PRIMARY KEY,
    doctorID INT REFERENCES Doctors(doctorID) ON DELETE CASCADE,
    patientID INT REFERENCES Patients(patientID) ON DELETE CASCADE,
    appointmentDate DATE NOT NULL,
    appointmentTime TIME NOT NULL,
    appointmentStatus ENUM('Scheduled', 'Cancelled', 'Completed') NOT NULL
);
