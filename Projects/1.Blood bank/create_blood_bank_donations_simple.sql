-- Create blood_bank_donations table without foreign key constraints
CREATE TABLE IF NOT EXISTS `blood_bank_donations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `donor_id` int(11) NOT NULL,
  `blood_type` varchar(10) NOT NULL,
  `units` int(11) NOT NULL DEFAULT 1,
  `donation_center` varchar(255) DEFAULT NULL,
  `donation_date` date NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `donor_id` (`donor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create migration script to move existing blood bank donations to the new table
-- Only run this if the blood_donations table exists and has donation_type field
INSERT INTO blood_bank_donations (
  donor_id, 
  blood_type, 
  units, 
  donation_center, 
  donation_date, 
  status, 
  notes, 
  created_at, 
  updated_at
)
SELECT 
  donor_id, 
  blood_type, 
  units, 
  donation_center, 
  donation_date, 
  status, 
  notes, 
  created_at, 
  updated_at
FROM blood_donations
WHERE donation_type = 'blood_bank';

-- You may want to remove the migrated records from the original table
-- Uncomment the following line if you want to delete the migrated records:
-- DELETE FROM blood_donations WHERE donation_type = 'blood_bank'; 