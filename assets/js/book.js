document.addEventListener('DOMContentLoaded', function () {
    const bookmarkBtn = document.getElementById('bookmarkBtn');
    const bookmarkStatus = document.getElementById('bookmark-status');
    const searchInput = document.getElementById('chapterSearch');
    const chapterList = document.getElementById('chapterList');

    if (bookmarkBtn) {
        bookmarkBtn.addEventListener('click', async function () {
            if (!window.bookData.isLoggedIn) {
                alert('Please log in first.');
                return;
            }

            const bookId = window.bookData.bookId;
            const current = parseInt(bookmarkBtn.getAttribute('data-bookmarked'), 10);

            try {
                const response = await fetch('bookmark_toggle.php', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        book_id: bookId,
                        current: current
                    }),
                    credentials: 'same-origin'
                });

                const data = await response.json();
                if (data.success) {
                    const newStatus = data.bookmarked ? 1 : 0;
                    bookmarkBtn.setAttribute('data-bookmarked', newStatus);
                    bookmarkBtn.textContent = newStatus ? 'Bookmarked' : 'Bookmark';

                    bookmarkStatus.innerHTML = newStatus
                        ? '<span class="success-tick">✔️ Bookmarked</span>'
                        : '<span class="remove-tick">❌ Bookmark Removed</span>';
                    bookmarkStatus.style.display = 'inline-block';

                    setTimeout(() => {
                        bookmarkStatus.style.display = 'none';
                        bookmarkStatus.innerHTML = '';
                    }, 2000);
                } else {
                    console.error('Server error:', data.error);
                }
            } catch (error) {
                console.error('Network error:', error);
            }
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const searchValue = parseInt(this.value, 10);
            const chapters = chapterList.querySelectorAll('.chapter-card');

            chapters.forEach(card => {
                const chapterno = parseInt(card.getAttribute('data-chapterno'), 10);
                if (!this.value || chapterno === searchValue) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});
