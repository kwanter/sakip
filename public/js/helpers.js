(function(window){
  'use strict';

  function toIDCurrency(value){
    if (value === undefined || value === null) return '';
    const numeric = String(value).replace(/[^0-9]/g, '');
    if (numeric === '') return '';
    return Number(numeric).toLocaleString('id-ID');
  }

  function stripCurrency(value){
    if (value === undefined || value === null) return '';
    return String(value).replace(/[^0-9]/g, '');
  }

  function attachCurrencyInputFormatting(selector){
    const el = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (!el) return;
    el.addEventListener('input', function(){
      const v = stripCurrency(this.value);
      this.value = toIDCurrency(v);
    });
  }

  function initialCurrencyFormat(selector){
    const el = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (!el) return;
    const v = stripCurrency(el.value);
    el.value = toIDCurrency(v);
  }

  function sanitizeCurrencyBeforeSubmit(formSelector, inputSelector){
    const form = typeof formSelector === 'string' ? document.querySelector(formSelector) : formSelector;
    const input = typeof inputSelector === 'string' ? document.querySelector(inputSelector) : inputSelector;
    if (!form || !input) return;
    form.addEventListener('submit', function(){
      const v = stripCurrency(input.value);
      input.value = v;
    });
  }

  function validateYearRange(startSelector, endSelector){
    const start = document.querySelector(startSelector);
    const end = document.querySelector(endSelector);
    if (!start || !end) return;
    function check(){
      const s = parseInt(start.value);
      const e = parseInt(end.value);
      if (s && e && e < s){
        alert('Tahun selesai tidak boleh lebih kecil dari tahun mulai.');
        end.value = s + 1;
      }
    }
    start.addEventListener('change', check);
    end.addEventListener('change', check);
  }

  function validateDateRange(startSelector, endSelector){
    const start = document.querySelector(startSelector);
    const end = document.querySelector(endSelector);
    if (!start || !end) return;
    function check(){
      const s = start.value;
      const e = end.value;
      if (s && e && s > e){
        alert('Tanggal mulai tidak boleh lebih besar dari tanggal selesai');
        end.value = '';
      }
    }
    start.addEventListener('change', check);
    end.addEventListener('change', check);
  }

  function setStatusOptions(selectEl, entity){
    const map = {
      kegiatan: [
        {value: 'draft', label: 'Draft'},
        {value: 'berjalan', label: 'Aktif'},
        {value: 'selesai', label: 'Selesai'},
        {value: 'tunda', label: 'Tunda'}
      ],
      program: [
        {value: 'draft', label: 'Draft'},
        {value: 'aktif', label: 'Aktif'},
        {value: 'selesai', label: 'Selesai'}
      ],
      instansi: [
        {value: 'aktif', label: 'Aktif'},
        {value: 'nonaktif', label: 'Non-aktif'}
      ],
      indikator: [
        {value: 'aktif', label: 'Aktif'},
        {value: 'nonaktif', label: 'Non-aktif'}
      ]
    };
    const opts = map[entity] || [];
    if (!selectEl) return;
    // If select has no options or needs normalization, rebuild
    if (selectEl.dataset.normalized === 'true') return;
    const currentValue = selectEl.value || '';
    selectEl.innerHTML = '';
    opts.forEach(function(o){
      const opt = document.createElement('option');
      opt.value = o.value;
      opt.textContent = o.label;
      if (currentValue && currentValue === o.value){
        opt.selected = true;
      }
      selectEl.appendChild(opt);
    });
    selectEl.dataset.normalized = 'true';
  }

  window.Helpers = {
    toIDCurrency,
    stripCurrency,
    attachCurrencyInputFormatting,
    initialCurrencyFormat,
    sanitizeCurrencyBeforeSubmit,
    validateYearRange,
    validateDateRange,
    setStatusOptions
  };
})(window);