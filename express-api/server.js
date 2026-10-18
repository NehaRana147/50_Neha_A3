
const express = require("express");

const app = express();
const PORT = 3000;

// REST API endpoint
app.get("/api/students", (req, res) => {
    res.json([
        {
            id: 1,
            name: "Rahul Patel",
            course: "PHP"
        },
        {
            id: 2,
            name: "Priya Shah",
            course: "Python"
        },
        {
            id: 3,
            name: "Amit Desai",
            course: "Java"
        }
    ]);
});


app.get("/api/php-students", async (req, res) => {
    try {
        const response = await fetch(
            "http://localhost/50_Neha_A3/Q6-express-call-php/students_api.php"
        );

        if (!response.ok) {
            return res.status(502).json({
                error: "PHP API request failed."
            });
        }

        const students = await response.json();

        res.json({
            message: "Data received from PHP REST API",
            students: students
        });

    } catch (error) {
        res.status(500).json({
            error: "Could not connect to PHP API."
        });
    }
});


app.listen(PORT, () => {
    console.log(`Express API running at http://localhost:${PORT}`);
});
