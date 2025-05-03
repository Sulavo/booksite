document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const suggestionsBox = document.getElementById('suggestions');

    let debounceTimeout;

    function highlightMatch(text, query) {
        const words = query.toLowerCase().split(/\s+/).filter(Boolean);
        if (words.length === 0) return text;

        const regex = new RegExp(`(${words.join('|')})`, 'gi');
        return text.replace(regex, '<span class="highlight">$1</span>');
    }

    function fetchSuggestions(query) {
        fetch('/booksite/includes/inverted_index.php?query=' + encodeURIComponent(query))
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok.');
                return response.json();
            })
            .then(data => {
                suggestionsBox.innerHTML = '';

                if (!data || data.length === 0) {
                    suggestionsBox.style.display = 'none';
                    return;
                }

                const visibleCount = Math.min(data.length, 5);

                for (let i = 0; i < visibleCount; i++) {
                    const book = data[i];
                    const item = document.createElement('div');
                    item.className = 'suggestion-item';

                    item.innerHTML = `
                        <img src="/booksite/assets/images/books/${book.image || 'default.png'}" alt="${book.title}">
                        <div class="suggestion-text">
                            <div class="matched-title">${highlightMatch(book.title, query)}</div>
                            ${book.author_name ? `<small>by ${book.author_name}</small>` : ''}
                        </div>
                    `;

                    item.addEventListener('click', function () {
                        window.location.href = '/booksite/book.php?id=' + book.id;
                    });

                    suggestionsBox.appendChild(item);
                }

                if (data.length > 5) {
                    const seeMore = document.createElement('div');
                    seeMore.className = 'suggestion-item see-more';
                    seeMore.textContent = 'See All...';

                    seeMore.addEventListener('click', function () {
                        const query = searchInput.value.trim();
                        if (query.length > 0) {
                            window.location.href = '/booksite/search.php?q=' + encodeURIComponent(query);
                        }
                    });

                    suggestionsBox.appendChild(seeMore);
                }

                suggestionsBox.style.display = 'block';
            })
            .catch(function (error) {
                console.error('Error fetching suggestions:', error);
                suggestionsBox.style.display = 'none';
            });
    }

    searchInput.addEventListener('input', function () {
        const query = this.value.trim();

        clearTimeout(debounceTimeout);

        if (query.length === 0) {
            suggestionsBox.style.display = 'none';
            suggestionsBox.innerHTML = '';
            return;
        }

        debounceTimeout = setTimeout(() => {
            fetchSuggestions(query);
        }, 300); 
    });

    document.addEventListener('click', function (e) {
        if (!document.getElementById('navbar').contains(e.target)) {
            suggestionsBox.style.display = 'none';
        }
    });
});
