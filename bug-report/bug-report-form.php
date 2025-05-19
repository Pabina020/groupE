<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Bug Report Form</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      background-color: #f7f7f7;
      margin: 0;
    }
    .form-container {
      background: white;
      padding: 50px 40px;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
      width: 500px; /* Increased width */
    }
    .form-container h2 {
      text-align: center;
      margin-bottom: 15px;
      font-size: 1.8em;
    }
    .form-container p {
      text-align: center;
      font-size: 1em;
      color: gray;
      margin-bottom: 30px;
    }
    label {
      display: block;
      margin-top: 20px;
      font-weight: bold;
    }
    input[type="email"],
    input[type="file"],
    select,
    textarea {
      width: 100%;
      padding: 12px 15px;
      margin-top: 8px;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: 1em;
    }
    textarea {
      resize: vertical;
      min-height: 100px;
    }
    button {
      width: 100%;
      padding: 14px;
      margin-top: 30px;
      background-color: #22c55e;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 1em;
    }
    button:hover {
      background-color: #16a34a;
    }
    .file-upload {
      border: 2px dashed #ddd;
      padding: 25px;
      text-align: center;
      border-radius: 6px;
      cursor: pointer;
      color: #555;
      margin-top: 8px;
    }
  </style>
</head>
<body>
  <div class="form-container">
    <h2>🐛 Bug report form</h2>
    <p>Use this form to report any bugs or issues you encounter.</p>
    <form action="submit-bug.php" method="POST" enctype="multipart/form-data">
      
      <label for="email">Your email *</label>
      <input type="email" id="email" name="email" placeholder="you@example.com" required>

      <label for="description">Brief description of the issue</label>
      <textarea id="description" name="description" placeholder="Describe the issue here..." required></textarea>

      <label for="severity">Severity of the issue</label>
      <select name="severity" id="severity" required>
        <option value="">Select Severity</option>
        <option value="Low">Low</option>
        <option value="Medium">Medium</option>
        <option value="High">High</option>
      </select>

      <label for="bug_image">Screenshot of the issue</label>
      <div class="file-upload">
        Drag & drop a file or <input type="file" name="bug_image" id="bug_image" accept="image/*">
      </div>

      <button type="submit">Submit Bug Report</button>
    </form>
  </div>
</body>
</html>