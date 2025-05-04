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
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: white;
      padding: 30px 40px;
      border-radius: 12px;
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
      z-index: 1000;
      font-size: 20px;
    }

    #closeBtn {
      display: inline-block;
      margin-top: 20px;
      padding: 8px 16px;
      background: #e74c3c;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 16px;
    }

    #closeBtn:hover {
      background: #c0392b;
    }
  </style>
</head>
<body>
  <h1>📡 RFID Exit Monitoring</h1>
  <div id="alertModal">
    <span id="alertText">Book taken outside</span><br/>
    <button id="closeBtn">Dismiss</button>
  </div>

  <script>
    let lastSeenTime = null;

    function showModal(message) {
      const modal = document.getElementById('alertModal');
      const text = document.getElementById('alertText');
      text.textContent = message;
      modal.style.display = 'block';
    }

    function hideModal() {
      document.getElementById('alertModal').style.display = 'none';
    }

    document.getElementById('closeBtn').addEventListener('click', hideModal);

    setInterval(() => {
      fetch('../Controllers/ExitZoneController.php')
        .then(response => response.json())
        .then(data => {
          if (data.length > 0) {
            const latest = data[0];
            const timestamp = latest.scanned_at;
            const bookTitle = latest.book_title;

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
