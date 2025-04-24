<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Exit Monitor</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      text-align: center;
      padding-top: 50px;
      background: #f0f0f0;
    }

    #alertModal {
      display: none;
      position: fixed;
      top: 30px;
      left: 50%;
      transform: translateX(-50%);
      background: white;
      padding: 20px 30px;
      border-radius: 10px;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
      z-index: 1000;
      font-size: 18px;
    }
  </style>
</head>
<body>
  <h1>📡 RFID Exit Monitoring</h1>
  <div id="alertModal"><span id="alertText">Book taken outside</span></div>

  <script>
    let lastSeenTime = null; // In-memory only — reset on page reload
  
    function showModal(message) {
      const modal = document.getElementById('alertModal');
      const text = document.getElementById('alertText');
      text.textContent = message;
      modal.style.display = 'block';
      setTimeout(() => {
        modal.style.display = 'none';
      }, 5000);
    }
  
    setInterval(() => {
      fetch('../Controllers/ExitZoneController.php')
        .then(response => response.json())
        .then(data => {
          if (data.length > 0) {
            const latest = data[0];
            const timestamp = latest.scanned_at;
            const bookTitle = latest.book_title;
  
            // Only show if this is a NEW scan (not on page load)
            if (timestamp && timestamp !== lastSeenTime) {
              if (lastSeenTime !== null) {
                showModal(`📦 Book "${bookTitle}" was taken outside`);
              }
              lastSeenTime = timestamp;
            }
          }
        })
        .catch(error => console.error('Fetch error:', error));
    }, 1000);
  </script>
  
</body>
</html>
