document.addEventListener('DOMContentLoaded', function() {
    const newsList = document.getElementById('news-list');
    newsList.innerHTML = '<div style="text-align:center;margin:40px 0;">Loading news...</div>';
    fetch('../backend/get_news.php')
        .then(response => response.json())
        .then(data => {
            if (!data.length) {
                newsList.innerHTML = '<p>No news articles found.</p>';
                return;
            }
            newsList.innerHTML = '';
            data.forEach(article => {
                newsList.innerHTML += `<article class="news-article">
                    <h3 class="news-title">${article.title}</h3>
                    <div class="news-date">${new Date(article.published_at).toLocaleDateString()}</div>
                    <div class="news-content">${article.content}</div>
                </article>`;
            });
        })
        .catch(() => {
            newsList.innerHTML = '<p style="color:red;">Error loading news articles.</p>';
        });
});
