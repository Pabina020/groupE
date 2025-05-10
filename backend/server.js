<<<<<<< HEAD
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

// Utility: Read and write users
const readUsers = () => fs.existsSync(USERS_FILE) ? JSON.parse(fs.readFileSync(USERS_FILE)) : [];
const writeUsers = (users) => fs.writeFileSync(USERS_FILE, JSON.stringify(users, null, 2));

// Signup
app.post('/signup', async (req, res) => {
    const { username, email, password, role } = req.body;
    if (!username || !email || !password || !role) {
        return res.status(400).json({ message: 'All fields required' });
    }

    const users = readUsers();
    if (users.find(u => u.email === email)) {
        return res.status(409).json({ message: 'User already exists' });
    }

    const hashedPassword = await bcrypt.hash(password, 10);
    users.push({ username, email, password: hashedPassword, role });
    writeUsers(users);
    res.status(201).json({ message: 'Signup successful' });
});

// Login
app.post('/login', async (req, res) => {
    const { email, password } = req.body;
    const users = readUsers();
    const user = users.find(u => u.email === email);
    if (!user) return res.status(401).json({ message: 'Invalid email or password' });

    const match = await bcrypt.compare(password, user.password);
    if (!match) return res.status(401).json({ message: 'Invalid email or password' });

    res.json({ message: 'Login successful', role: user.role });
});

app.listen(PORT, () => console.log(`✅ Server running on http://localhost:${PORT}`));
=======
const express = require('express');
const fs = require('fs');
const path = require('path');
const bcrypt = require('bcrypt');
const cors = require('cors');
const cookieParser = require('cookie-parser');

const app = express();
const PORT = 3001;
const USERS_FILE = path.join(__dirname, 'users.json');

// Middleware
app.use(express.json());
app.use(cookieParser());
app.use(cors({ origin: 'http://127.0.0.1:5500', credentials: true })); // ✅ allow Live Server

// Serve static files (optional)
app.use(express.static(__dirname));

// Read and write user utility
const readUsers = () => {
  if (!fs.existsSync(USERS_FILE)) return [];
  const data = fs.readFileSync(USERS_FILE, 'utf-8');
  return JSON.parse(data);
};
const writeUsers = (users) => {
  fs.writeFileSync(USERS_FILE, JSON.stringify(users, null, 2));
};

// Signup route
app.post('/signup', async (req, res) => {
  const { username, email, password, role } = req.body;
  if (!username || !email || !password || !role) {
    return res.status(400).json({ message: 'All fields required' });
  }

  const users = readUsers();
  if (users.find(u => u.email === email)) {
    return res.status(409).json({ message: 'User already exists' });
  }

  const hashedPassword = await bcrypt.hash(password, 10);
  users.push({ username, email, password: hashedPassword, role });
  writeUsers(users);

  res.status(201).json({ message: 'Signup successful' });
});

// Login route
app.post('/login', async (req, res) => {
  const { email, password } = req.body;
  const users = readUsers();
  const user = users.find(u => u.email === email);

  if (!user) return res.status(401).json({ message: 'Invalid email or password' });

  const match = await bcrypt.compare(password, user.password);
  if (!match) return res.status(401).json({ message: 'Invalid email or password' });

  // Set cookie with user info
  res.cookie('user', JSON.stringify({ name: user.username, email: user.email }), {
    maxAge: 86400000, // 1 day
    httpOnly: false,
    sameSite: 'Lax',
  });

  res.json({ message: 'Login successful', role: user.role });
});

// Logout route
app.get('/logout', (req, res) => {
  res.clearCookie('user');
  res.status(200).json({ message: 'Logged out' });
});

// Start server
app.listen(PORT, () => {
  console.log(`✅ Server running on http://localhost:${PORT}`);
});
>>>>>>> origin/main
