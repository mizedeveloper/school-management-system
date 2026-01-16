<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>About the Project — S.E.A 🌊</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background-color: #0e0e0e;
      color: #eaeaea;
      line-height: 1.6;
    }

    header {
      background-color: #111;
      padding: 40px 20px;
      text-align: center;
      border-bottom: 1px solid #222;
    }

    header h1 {
      font-size: 2.4rem;
      color: #00cfff;
      margin-bottom: 10px;
    }

    header p {
      font-size: 1.1rem;
      color: #aaa;
    }

    .container {
      max-width: 900px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .section {
      margin-bottom: 45px;
    }

    .section h2 {
      font-size: 1.7rem;
      color: #00cfff;
      margin-bottom: 15px;
      border-left: 4px solid #00cfff;
      padding-left: 10px;
    }

    ul {
      padding-left: 20px;
    }

    li {
      margin-bottom: 8px;
    }

    .highlight {
      color: #5bd3ff;
      font-weight: 600;
    }

    .links a {
      display: inline-block;
      margin-right: 20px;
      color: #00cfff;
      font-weight: 600;
      text-decoration: none;
    }

    .links a:hover {
      text-decoration: underline;
    }

    footer {
      text-align: center;
      padding: 20px;
      font-size: 0.9rem;
      color: #777;
      background-color: #111;
      border-top: 1px solid #222;
    }

    code {
      background-color: #222;
      padding: 2px 5px;
      border-radius: 4px;
      font-family: monospace;
    }
  </style>
</head>
<body>

  <header>
    <h1>S.E.A — Sistema de Educação e Aprendizado</h1>
    <p>Web-based education management platform</p>
  </header>

  <div class="container">

    <div class="section">
      <h2>Project Summary</h2>
      <p>
        S.E.A is a <span class="highlight">web-based school management system</span> designed to manage students, teachers, classes, activities, and administrative tasks in a single platform.
      </p>
      <p>
        The system supports multiple teachers and students per class, while a single administrator manages user creation, passwords, and overall system control. It is designed for educational portfolio purposes, demonstrating real-world system logic and user management.
      </p>
    </div>

    <div class="section">
      <h2>Key Features</h2>
      <ul>
        <li>Single admin account (<code>admin / admin123</code>) with access to all user passwords for reference; passwords never change</li>
        <li>Automatic generation of usernames and passwords for teachers (<code>p1, p2...</code>) and students (<code>a1, a2...</code>)</li>
        <li>Teachers can manage multiple classes, students, activities, and incidents</li>
        <li>Students can participate in multiple classes and submit tasks to teachers</li>
        <li>Profile customization for teachers and students (photo upload)</li>
        <li>Administrator dashboard for user management and system overview</li>
        <li>File upload and download for activities and assignments</li>
        <li>Role-based access control: Admin, Teacher, Student</li>
      </ul>
    </div>

    <div class="section">
      <h2>Technologies</h2>
      <ul>
        <li>HTML, CSS, JavaScript</li>
        <li>PHP</li>
        <li>MySQL / MariaDB</li>
      </ul>
    </div>

    <div class="section">
      <h2>System Context & Security Notes</h2>
      <p>
        This project was developed as a <span class="highlight">portfolio-focused educational platform</span>. The interface is in Portuguese, reflecting its intended use in a local school context.
      </p>
      <p>
        The main administrator account is <span class="highlight">unique and shared among trusted users</span>. Multiple sessions are allowed simultaneously, but the admin's role is purely administrative. Teachers and students have richer, customizable interfaces. The system demonstrates real-world user management, class structure, task handling, and role-specific features.
      </p>
      <p>
        ⚠️ <span class="highlight">Security Notes:</span>
        <ul>
          <li>Default admin credentials (<code>admin / admin123</code>) are for demonstration only. Change immediately in production.</li>
          <li>User passwords are stored encrypted in the database but visible in the admin panel for reference; ensure proper encryption keys in a production environment.</li>
          <li>Database connection settings (e.g., in <code>db.php</code>) and encryption keys (e.g., in <code>config.php</code>) are set for local testing and must be updated for secure deployment.</li>
          <li>This project is intended for portfolio and educational use; further security hardening is required before any real-world deployment.</li>
        </ul>
      </p>
    </div>

    <div class="section">
      <h2>Developer</h2>
      <p>
        Developed by <span class="highlight">Moisés Caricchio</span>, IT Support Technician and Junior Developer with experience in technical support, system maintenance, automation, and software development.
      </p>

      <div class="links">
        <a href="https://github.com/mizedeveloper" target="_blank">GitHub</a>
        <a href="https://linkedin.com/in/moisescaricchio" target="_blank">LinkedIn</a>
      </div>
    </div>

  </div>

  <footer>
    © <?= date('Y') ?> Moisés Caricchio — Portfolio Project
  </footer>

</body>
</html>
