# School Attendance System

A QR Code-based attendance system for schools that allows marking attendance by scanning student QR codes and generates monthly attendance reports.

## Features

- QR Code-based attendance marking
- Student management (add, view, delete)
- Monthly attendance reports
- Real-time attendance tracking
- Modern and responsive UI

## Requirements

- Python 3.7 or higher
- Web browser with camera access
- SQLite (included with Python)

## Installation

1. Clone this repository
2. Install the required packages:
   ```
   pip install -r requirements.txt
   ```

## Usage

1. Start the application:
   ```
   python app.py
   ```

2. Open your web browser and go to `http://localhost:5000`

3. Add students:
   - Click on "Students" in the navigation menu
   - Click "Add New Student"
   - Fill in the student details
   - The system will generate a unique QR code for each student

4. Mark attendance:
   - Click on "Scan QR" in the navigation menu
   - Allow camera access when prompted
   - Scan a student's QR code to mark their attendance

5. View reports:
   - Click on "Reports" in the navigation menu
   - Select the month and year
   - Click "Generate Report" to view the attendance summary

## Security

- Each student has a unique QR code
- Attendance can only be marked once per day per student
- All data is stored securely in a SQLite database

## Support

For any issues or questions, please contact the system administrator. "# DIF-Attendance-System" 
