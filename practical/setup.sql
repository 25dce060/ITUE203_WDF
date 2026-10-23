
CREATE DATABASE IF NOT EXISTS student_db;
USE student_db;

CREATE TABLE IF NOT EXISTS students (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(60) NOT NULL,
    last_name VARCHAR(60) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    phone VARCHAR(15) NOT NULL,
    course VARCHAR(80) NOT NULL,
    year_level TINYINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_deleted TINYINT(1) NOT NULL DEFAULT 0
);

INSERT IGNORE INTO students
(first_name, last_name, email, phone, course, year_level)
VALUES
('Aarav','Shah','aarav.shah@example.com','9876543210','B.Tech',1),
('Diya','Patel','diya.patel@example.com','9876543211','BCA',2),
('Rohan','Desai','rohan.desai@example.com','9876543212','MCA',1),
('Anaya','Mehta','anaya.mehta@example.com','9876543213','B.Tech',3),
('Vivaan','Joshi','vivaan.joshi@example.com','9876543214','BCA',1),
('Isha','Trivedi','isha.trivedi@example.com','9876543215','MCA',2),
('Arjun','Modi','arjun.modi@example.com','9876543216','B.Tech',2),
('Sara','Kapadia','sara.kapadia@example.com','9876543217','BCA',3),
('Kabir','Raval','kabir.raval@example.com','9876543218','MCA',1),
('Myra','Bhatt','myra.bhatt@example.com','9876543219','B.Tech',4),
('Reyansh','Parikh','reyansh.parikh@example.com','9876543220','BCA',2),
('Aadhya','Vyas','aadhya.vyas@example.com','9876543221','MCA',3),
('Krish','Pandya','krish.pandya@example.com','9876543222','B.Tech',1),
('Kiara','Dave','kiara.dave@example.com','9876543223','BCA',4),
('Dhruv','Soni','dhruv.soni@example.com','9876543224','MCA',2),
('Navya','Thakkar','navya.thakkar@example.com','9876543225','B.Tech',3),
('Atharv','Gandhi','atharv.gandhi@example.com','9876543226','BCA',1),
('Pari','Shukla','pari.shukla@example.com','9876543227','MCA',4),
('Advik','Mistry','advik.mistry@example.com','9876543228','B.Tech',2),
('Zara','Nair','zara.nair@example.com','9876543229','BCA',3),
('Om','Panchal','om.panchal@example.com','9876543230','MCA',1),
('Mira','Desai','mira.desai@example.com','9876543231','B.Tech',4);
