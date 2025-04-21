const express = require('express');
const cors = require('cors');
const fs = require('fs');
const bcrypt = require('bcrypt');
const path = require('path');

const app = express();
const PORT = 3001;
const USERS_FILE = path.join(__dirname, 'users.json');

app.use(cors());
app.use(express.json());

// Utility: Read users from JSON
const readUsers = () => {
  if (!fs.existsSync(USERS_FILE)) return [];
  const data = fs.readFileSync(USERS_FILE, 'utf-8');
  return JSON.parse(data);
};

// Utility: Write users to JSON
const writeUsers = (users) => {
  fs.writeFileSync(USERS_FILE, JSON.stringify(users, null, 2));
};

// ✅ Signup Route
app.post('/signup', async (req, res) => {
  const { username, email, password, role } = req.body;
  console.log("📥 Signup request:", req.body);

  if (!username || !email || !password || !role) {
    return res.status(400).json({ message: 'All fields required' });
  }

  const users = readUsers();

  if (users.find(u => u.email === email)) {
    console.log("⚠️ Duplicate email:", email);
    return res.status(409).json({ message: 'User already exists' });
  }

  const hashedPassword = await bcrypt.hash(password, 10);
  users.push({ username, email, password: hashedPassword, role });
  writeUsers(users);

  console.log("✅ User registered:", email);
  res.status(201).json({ message: 'Signup successful' });
});

// ✅ Login Route
app.post('/login', async (req, res) => {
  const { email, password } = req.body;
  console.log("🔐 Login attempt:", email);

  const users = readUsers();
  const user = users.find(u => u.email === email);

  if (!user) {
    console.log("❌ Email not found:", email);
    return res.status(401).json({ message: 'Invalid email or password' });
  }

  const match = await bcrypt.compare(password, user.password);
  console.log("🧪 Password match:", match);

  if (!match) {
    console.log("❌ Wrong password for:", email);
    return res.status(401).json({ message: 'Invalid email or password' });
  }

  console.log("✅ Login successful for:", email);
  res.json({ message: 'Login successful', role: user.role });
});

// ✅ Start server
app.listen(PORT, () => {
  console.log(`✅ Server running on http://localhost:${PORT}`);
});
