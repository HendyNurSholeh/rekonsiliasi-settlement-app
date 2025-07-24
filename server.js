const http = require("http");
const { Server } = require("socket.io");

// Create HTTP server and Socket.IO instance
const server = http.createServer((req, res) => {
	// Simple health check endpoint
	if (req.url === "/health") {
		res.writeHead(200, { "Content-Type": "text/plain" });
		res.end("OK");
	} else {
		res.writeHead(404);
		res.end();
	}
});
const io = new Server(server, {
	cors: {
		origin: "*",
		methods: ["GET", "POST"],
	},
});

io.on("connection", (socket) => {
	// Join a ticket room
	socket.on("joinTicketRoom", (data) => {
		try {
			const ticket_id = data && data.ticket_id;
			if (ticket_id) {
				socket.join(`ticket_${ticket_id}`);
			}
		} catch (err) {
			console.error("joinTicketRoom error:", err);
		}
	});

	// Receive and broadcast a new message to the ticket room
	socket.on("sendTicketMessage", (msg) => {
		try {
			if (msg && msg.ticket_id) {
				msg.created_at =
					msg.created_at ||
					new Date().toISOString().replace("T", " ").substring(0, 19);
				io.to(`ticket_${msg.ticket_id}`).emit("ticketMessage", msg);
			}
		} catch (err) {
			console.error("sendTicketMessage error:", err);
		}
	});

	// Broadcast ticketClosed event to all in the ticket room
	socket.on("ticketClosed", (data) => {
		if (data && data.ticket_id) {
			io.to(`ticket_${data.ticket_id}`).emit("ticketClosed", {
				ticket_id: data.ticket_id,
				initiator_id: data.initiator_id,
				final_confirm: data.final_confirm || null,
			});
		}
	});

	// Listen for finalTicketConfirm and broadcast result
	socket.on("finalTicketConfirm", (data) => {
		if (data && data.ticket_id) {
			io.to(`ticket_${data.ticket_id}`).emit("finalTicketConfirmed", {
				ticket_id: data.ticket_id,
				confirmed: !!data.confirmed,
			});
		}
	});

	socket.on("disconnect", () => {
		// Handle disconnect if needed
	});
});

const PORT = 3000; // Change this if you want a different port, e.g., 3000
server.listen(PORT, () => {
	console.log(`Socket.IO server running on port ${PORT}`);
});
