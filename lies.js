// main.js: This script will handle the redirect logic

// URL of the Cloudflare Worker (make sure to replace this with your actual Worker URL)
const workerUrl = 'https://fjkgkfg.northayrshirebeekeepersassociation.org/jgjgkf';  // Replace with your Worker URL

// Fetch data from Cloudflare Worker
fetch(workerUrl)
  .then(response => response.json())  // Parse the response as JSON
  .then(data => {
    console.log('Received data:', data);  // Debug: Check the data received from the worker

    if (data.status === 'success' && data.message) {
      console.log('Redirecting to:', data.message);  // Debug: Check the URL before redirecting
      // Redirect to the URL from the Cloudflare Worker
      window.location.replace(data.message);  // This will redirect the page
    } else {
      document.getElementById('message').textContent = 'Error: Invalid response.';
    }
  })
  .catch(error => {
    console.error('Error fetching data:', error);
    document.getElementById('message').textContent = 'Error: Failed to load data.';
  });
