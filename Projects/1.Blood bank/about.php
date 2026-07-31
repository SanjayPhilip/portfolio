<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - E Blood Connect</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .about-section {
            padding: 60px 0;
        }
        
        .about-content {
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.8;
        }
        
        .about-content p {
            margin-bottom: 20px;
        }
        
        .mission-vision {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            margin: 40px 0;
        }
        
        .mission-box, .vision-box {
            flex: 1;
            min-width: 300px;
            padding: 30px;
            border-radius: 10px;
            box-shadow: var(--box-shadow);
            background-color: white;
        }
        
        .mission-box h3, .vision-box h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .blood-facts {
            margin: 40px 0;
            background-color: white;
            border-radius: 10px;
            box-shadow: var(--box-shadow);
            padding: 30px;
        }
        
        .fact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .fact-item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }
        
        .fact-icon {
            font-size: 1.5rem;
            color: var(--primary-color);
        }
        
        .team-section {
            margin: 40px 0;
        }
        
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }
        
        .team-member {
            text-align: center;
            background-color: white;
            border-radius: 10px;
            box-shadow: var(--box-shadow);
            padding: 30px;
        }
        
        .member-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: #f5f5f5;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: var(--primary-color);
        }
        
        .member-name {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .member-role {
            color: var(--secondary-color);
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="page-banner">
        <div class="container">
            <h1>About Us</h1>
        </div>
    </div>
    
    <div class="container">
        <div class="about-section">
            <div class="about-content">
                <h2 class="section-title">Our Story</h2>
                <p>E Blood Connect was founded in 2023 with a simple mission: to ensure that no one faces a shortage of blood during medical emergencies. Our founders, a group of healthcare professionals and technology enthusiasts, recognized the urgent need for a streamlined system to connect blood donors with those in need.</p>
                <p>What started as a local initiative has grown into a comprehensive platform that facilitates life-saving connections between donors and recipients. We believe that something as essential as blood should be readily available to everyone, regardless of their location or circumstances.</p>
                <p>Today, E Blood Connect serves as a vital bridge in the healthcare ecosystem, leveraging technology to make blood donation and requests more accessible, efficient, and transparent.</p>
            </div>
            
            <div class="mission-vision">
                <div class="mission-box">
                    <h3><i class="fas fa-bullseye"></i> Our Mission</h3>
                    <p>To create a seamless connection between blood donors and recipients, ensuring timely access to safe blood for all those in need, while promoting a culture of regular voluntary blood donation across communities.</p>
                </div>
                
                <div class="vision-box">
                    <h3><i class="fas fa-eye"></i> Our Vision</h3>
                    <p>A world where no life is lost due to lack of access to blood, where blood donation is a regular practice, and where technology enables instant matching of donors with recipients in times of need.</p>
                </div>
            </div>
            
            <div class="blood-facts">
                <h2 class="section-title">Blood Donation Facts</h2>
                
                <div class="fact-grid">
                    <div class="fact-item">
                        <div class="fact-icon">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <div>
                            <h4>One donation can save up to three lives</h4>
                            <p>A single blood donation can be separated into three components: red blood cells, platelets, and plasma.</p>
                        </div>
                    </div>
                    
                    <div class="fact-item">
                        <div class="fact-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <h4>Every two seconds someone needs blood</h4>
                            <p>Blood is needed for accident victims, surgery patients, and those battling diseases like cancer.</p>
                        </div>
                    </div>
                    
                    <div class="fact-item">
                        <div class="fact-icon">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <div>
                            <h4>Blood regenerates quickly</h4>
                            <p>The body replaces plasma within 24 hours, and red blood cells within a few weeks.</p>
                        </div>
                    </div>
                    
                    <div class="fact-item">
                        <div class="fact-icon">
                            <i class="fas fa-tint"></i>
                        </div>
                        <div>
                            <h4>O-negative is universal</h4>
                            <p>O-negative blood can be given to anyone in an emergency, making it the most versatile blood type.</p>
                        </div>
                    </div>
                    
                    <div class="fact-item">
                        <div class="fact-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div>
                            <h4>Healthy adults can donate every 3 months</h4>
                            <p>After donating, your body needs time to replenish red blood cells before you can donate again.</p>
                        </div>
                    </div>
                    
                    <div class="fact-item">
                        <div class="fact-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div>
                            <h4>Donating is good for your health</h4>
                            <p>Regular blood donation can reduce iron levels, which may help lower the risk of heart disease.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="team-section">
                <h2 class="section-title">Our Team</h2>
                
                <div class="team-grid">
                    <div class="team-member">
                        <div class="member-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="member-name">Dr. Sarah Johnson</div>
                        <div class="member-role">Founder & Medical Director</div>
                        <p>Hematologist with over 15 years of experience in blood banking and transfusion medicine.</p>
                    </div>
                    
                    <div class="team-member">
                        <div class="member-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="member-name">Michael Rodriguez</div>
                        <div class="member-role">Technical Lead</div>
                        <p>Software engineer passionate about using technology to solve healthcare challenges.</p>
                    </div>
                    
                    <div class="team-member">
                        <div class="member-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="member-name">Priya Patel</div>
                        <div class="member-role">Community Outreach Director</div>
                        <p>Public health advocate focused on increasing blood donation awareness in underserved communities.</p>
                    </div>
                    
                    <div class="team-member">
                        <div class="member-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="member-name">David Chen</div>
                        <div class="member-role">Operations Manager</div>
                        <p>Logistics expert ensuring smooth coordination between donors, recipients, and medical facilities.</p>
                    </div>
                </div>
            </div>
            
            <div class="about-content">
                <h2 class="section-title">Join Our Cause</h2>
                <p>E Blood Connect is more than just a platform—it's a community of life-savers. Whether you're a donor, a healthcare professional, or someone passionate about our mission, there are many ways to get involved:</p>
                <ul>
                    <li>Become a regular blood donor</li>
                    <li>Spread awareness about the importance of blood donation</li>
                    <li>Volunteer at our blood drives and events</li>
                    <li>Partner with us as a healthcare institution</li>
                    <li>Support our technological development</li>
                </ul>
                <p>Together, we can create a world where blood is readily available for everyone who needs it.</p>
                <p class="text-center" style="margin-top: 30px;">
                    <a href="register.php" class="btn primary-btn">Join Us Today</a>
                </p>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="js/script.js"></script>
</body>
</html> 