<?php
    $selectView = 'SELECT 
                        appointments.appointment_id, 
                        appointments.timestamp, 
                        appointments.length, 
                        appointments.confirmed, 
                        doctors.doctor_id, 
                        doctors.given_name AS doctor_given_name,
                        doctors.family_name AS doctor_family_name,
                        doctors.email AS doctor_email,
                        patients.patient_id,
                        patients.given_name AS patient_given_name,
                        patients.family_name AS patient_family_name,
                        patients.email AS patient_email
                    FROM appointments
                    JOIN doctors ON appointments.doctor_id = doctors.doctor_id
                    JOIN patients ON appointments.patient_id = patients.patient_id
                    WHERE appointments.timestamp > UNIX_TIMESTAMP()';
