
(function () {
    const table = document.getElementById('dataGrid');
    const input = document.getElementById('searchInput');
    const clearBtn = document.getElementById('clearSearch');
    if (!table || !input) return;
  
    
    const SEARCH_COLS = [1, 2];
  
    let t = null;
    input.addEventListener('input', () => {
      clearTimeout(t);
      t = setTimeout(() => filterTable(input.value.trim()), 120);
    });
  
    clearBtn?.addEventListener('click', () => {
      input.value = '';
      filterTable('');
      input.focus();
    });
  
    function unmark(td) {
      td.querySelectorAll('mark').forEach(m => {
        const text = document.createTextNode(m.textContent);
        m.replaceWith(text);
      });
    }
  
    function highlightCell(td, needle) {
      unmark(td);
      if (!needle) return;
      const txt = td.textContent;
      const safe = needle.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
      const re = new RegExp(safe, 'gi');
      td.innerHTML = txt.replace(re, m => `<mark>${m}</mark>`);
    }
  
    function filterTable(query) {
      const q = query.toLowerCase();
      const rows = table.tBodies[0]?.rows || [];
      for (let i = 0; i < rows.length; i++) {
        const tr = rows[i];
  
        
        SEARCH_COLS.forEach(ci => { const td = tr.cells[ci]; if (td) unmark(td); });
  
        if (!q) { tr.style.display = ''; continue; }
  
        let match = false;
        for (let j = 0; j < SEARCH_COLS.length; j++) {
          const ci = SEARCH_COLS[j];
          const td = tr.cells[ci];
          if (!td) continue;
          const text = (td.textContent || td.innerText || '').toLowerCase();
          if (text.indexOf(q) > -1) {
            match = true;
            highlightCell(td, query);
          }
        }
        tr.style.display = match ? '' : 'none';
      }
    }
  
    
    const params = new URLSearchParams(location.search);
    if (params.has('q') && params.get('q')) {
      input.value = params.get('q');
      filterTable(input.value);
    }
  })();
  