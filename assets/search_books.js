document.getElementById('bookSearch').addEventListener('input', function () {
  const query = this.value;
  if (query.length < 2) return;

  fetch('book_search.php?query=' + encodeURIComponent(query))
    .then(response => response.json())
    .then(data => {
      const suggestions = document.getElementById('suggestions');
      suggestions.innerHTML = '';
      if (data.length > 0) {
        suggestions.style.display = 'block';
      } else {
        suggestions.style.display = 'none';
      }

      data.forEach(book => {
        const div = document.createElement('div');
        div.textContent = `${book.title} (${book.book_id}) — ${book.author} (${book.year})`;
        div.classList.add('suggestion-item');
        div.onclick = () => {
          document.getElementById('bookSearch').value = book.title;  //prida ze na kliknuti muzu zvolit tu vybranou knihu
          const bookIdInput = document.getElementById('bookId');
          if (bookIdInput) {
            bookIdInput.value = book.book_id;
          }
          suggestions.innerHTML = '';
          suggestions.style.display = 'none';
        };
        suggestions.appendChild(div);
      });
    });
});