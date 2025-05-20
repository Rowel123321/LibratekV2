<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Exit Monitor</title>
  <link rel="stylesheet" href="../CSS/styles.css" />
 
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
                showModal(`⚠️ Book "${bookTitle}" was taken outside`);
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
