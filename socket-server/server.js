const express = require('express');
const app = express();
const http = require('http');
const server = http.createServer(app);
const { Server } = require("socket.io");
const cors = require('cors');

// Allow requests from the Laravel app
const io = new Server(server, {
  cors: {
    origin: "*", // allow all origins for local dev
    methods: ["GET", "POST"]
  }
});

app.use(cors());
app.use(express.json());

// Endpoint for Laravel to trigger a reminder
app.post('/api/emit-reminder', (req, res) => {
    const { judge_id, event_name, event_id } = req.body;
    
    if (!judge_id) {
        return res.status(400).json({ error: 'judge_id is required' });
    }

    // Emit the reminder event to all connected clients
    // The frontend will filter by judge_id or listen on a specific room
    io.emit(`judge-reminder-${judge_id}`, { 
        event_name,
        event_id,
        timestamp: new Date().toISOString()
    });

    console.log(`Reminder emitted to judge ${judge_id} for event ${event_name || 'Unknown'}`);
    res.json({ success: true });
});

io.on('connection', (socket) => {
    console.log('A user connected:', socket.id);
    
    socket.on('disconnect', () => {
        console.log('User disconnected:', socket.id);
    });
});

const PORT = process.env.PORT || 3000;
server.listen(PORT, () => {
  console.log(`Socket.IO Server running on port ${PORT}`);
});
