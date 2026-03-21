/* validation.js — Forms, image preview, toast notifications, spinner */
(function () {
  'use strict';

  /* ════════════════════════════════════════
     TOAST NOTIFICATIONS
  ════════════════════════════════════════ */
  window.showToast = function (message, type) {
    type = type || 'info'; // success | error | info
    var container = document.getElementById('toastContainer');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toastContainer';
      container.className = 'toast-container';
      document.body.appendChild(container);
    }

    var icons = { success: '✅', error: '❌', info: 'ℹ️' };
    var toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    toast.innerHTML =
      '<span class="toast-icon">' + (icons[type] || icons.info) + '</span>' +
      '<span class="toast-msg">'  + message + '</span>' +
      '<button class="toast-close" onclick="this.parentElement.remove()">✕</button>';

    container.appendChild(toast);

    setTimeout(function () {
      toast.classList.add('removing');
      setTimeout(function () { toast.remove(); }, 300);
    }, 4000);
  };

  /* ════════════════════════════════════════
     LOADING SPINNER ON BUTTON
  ════════════════════════════════════════ */
  window.setButtonLoading = function (btn, loading) {
    if (!btn) return;
    if (loading) {
      btn.classList.add('loading');
      btn.disabled = true;
    } else {
      btn.classList.remove('loading');
      btn.disabled = false;
    }
  };

  /* ════════════════════════════════════════
     IMAGE PREVIEW & MANAGEMENT
  ════════════════════════════════════════ */
  var selectedFiles = [];
  var MAX_IMAGES    = 2;

  function updatePreviews() {
    var grid    = document.getElementById('imagePreviews');
    var counter = document.getElementById('imageCounter');
    if (!grid) return;

    grid.innerHTML = '';

    selectedFiles.forEach(function (file, idx) {
      var reader = new FileReader();
      reader.onload = function (e) {
        var item = document.createElement('div');
        item.className = 'preview-item fade-up';
        item.innerHTML =
          '<img src="' + e.target.result + '" alt="Preview ' + (idx + 1) + '">' +
          '<button type="button" class="preview-remove" data-idx="' + idx + '" title="Remove">✕</button>';
        grid.appendChild(item);

        item.querySelector('.preview-remove').addEventListener('click', function () {
          selectedFiles.splice(idx, 1);
          updatePreviews();
          syncFileInput();
        });
      };
      reader.readAsDataURL(file);
    });

    if (counter) {
      counter.textContent = selectedFiles.length + ' / ' + MAX_IMAGES + ' image' + (selectedFiles.length !== 1 ? 's' : '') + ' selected';
      counter.className   = 'image-counter' + (selectedFiles.length === MAX_IMAGES ? ' limit' : '');
    }

    // Show/hide upload zone
    var zone = document.getElementById('uploadZone');
    if (zone) {
      zone.style.display = selectedFiles.length >= MAX_IMAGES ? 'none' : '';
    }
  }

  function syncFileInput() {
    // Build a new FileList-like DataTransfer
    var input = document.getElementById('imageInput');
    if (!input || !window.DataTransfer) return;
    var dt = new DataTransfer();
    selectedFiles.forEach(function (f) { dt.items.add(f); });
    input.files = dt.files;
  }

  function initImageUpload() {
    var input = document.getElementById('imageInput');
    var zone  = document.getElementById('uploadZone');
    if (!input || !zone) return;

    zone.addEventListener('click', function () { input.click(); });

    input.addEventListener('change', function () {
      var files = Array.from(input.files);
      var remaining = MAX_IMAGES - selectedFiles.length;

      if (files.length > remaining) {
        showToast('Maximum ' + MAX_IMAGES + ' images allowed.', 'error');
        files = files.slice(0, remaining);
      }
      selectedFiles = selectedFiles.concat(files);
      updatePreviews();
    });

    // Drag & drop
    zone.addEventListener('dragover', function (e) {
      e.preventDefault();
      zone.classList.add('dragover');
    });
    zone.addEventListener('dragleave', function () {
      zone.classList.remove('dragover');
    });
    zone.addEventListener('drop', function (e) {
      e.preventDefault();
      zone.classList.remove('dragover');
      var files = Array.from(e.dataTransfer.files).filter(function (f) {
        return f.type.startsWith('image/');
      });
      var remaining = MAX_IMAGES - selectedFiles.length;
      if (files.length > remaining) {
        showToast('Maximum ' + MAX_IMAGES + ' images allowed.', 'error');
        files = files.slice(0, remaining);
      }
      selectedFiles = selectedFiles.concat(files);
      updatePreviews();
      syncFileInput();
    });
  }

  /* ════════════════════════════════════════
     LOGIN FORM VALIDATION
  ════════════════════════════════════════ */
  function initLoginForm() {
    var form = document.getElementById('loginForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
      var valid = true;

      var email = form.querySelector('#email');
      var pass  = form.querySelector('#password');

      [email, pass].forEach(function (el) {
        if (el) {
          el.classList.remove('error');
          var errEl = el.parentElement.querySelector('.form-error');
          if (errEl) errEl.style.display = 'none';
        }
      });

      if (email && (!email.value.trim() || !email.value.includes('@'))) {
        email.classList.add('error');
        showError(email, 'Please enter a valid email address.');
        valid = false;
      }
      if (pass && pass.value.length < 6) {
        pass.classList.add('error');
        showError(pass, 'Password must be at least 6 characters.');
        valid = false;
      }

      if (!valid) {
        e.preventDefault();
        return;
      }

      var btn = form.querySelector('[type="submit"]');
      setButtonLoading(btn, true);
      // PHP will handle the actual submission; re-enable on error
    });
  }

  /* ════════════════════════════════════════
     REGISTER FORM VALIDATION
  ════════════════════════════════════════ */
  function initRegisterForm() {
    var form = document.getElementById('registerForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
      var valid = true;
      var fields = ['name','email','password','phone'];

      fields.forEach(function (id) {
        var el = form.querySelector('#' + id);
        if (el) {
          el.classList.remove('error');
        }
      });

      var name  = form.querySelector('#name');
      var email = form.querySelector('#email');
      var pass  = form.querySelector('#password');
      var phone = form.querySelector('#phone');

      if (name && name.value.trim().length < 2) {
        name.classList.add('error');
        showError(name, 'Please enter your full name.');
        valid = false;
      }
      if (email && (!email.value.trim() || !email.value.includes('@'))) {
        email.classList.add('error');
        showError(email, 'Please enter a valid email address.');
        valid = false;
      }
      if (pass && pass.value.length < 6) {
        pass.classList.add('error');
        showError(pass, 'Password must be at least 6 characters.');
        valid = false;
      }
      if (phone && phone.value.trim() && !/^[0-9+\-\s()]{7,15}$/.test(phone.value.trim())) {
        phone.classList.add('error');
        showError(phone, 'Please enter a valid phone number.');
        valid = false;
      }

      if (!valid) {
        e.preventDefault();
        return;
      }

      var btn = form.querySelector('[type="submit"]');
      setButtonLoading(btn, true);
    });
  }

  /* ════════════════════════════════════════
     SUBMIT COMPLAINT FORM VALIDATION
  ════════════════════════════════════════ */
  function initSubmitForm() {
    var form = document.getElementById('complaintForm');
    if (!form) return;

    // Mark select as having value
    var catSelect = form.querySelector('#category');
    if (catSelect) {
      catSelect.addEventListener('change', function () {
        catSelect.classList.toggle('has-value', catSelect.value !== '');
      });
    }

    form.addEventListener('submit', function (e) {
      var valid = true;

      var reqFields = form.querySelectorAll('[required]');
      reqFields.forEach(function (field) {
        field.classList.remove('error');
        if (!field.value.trim()) {
          field.classList.add('error');
          showError(field, 'This field is required.');
          valid = false;
        }
      });

      // Image: at least 1
      if (selectedFiles.length === 0) {
        showToast('Please upload at least 1 image.', 'error');
        valid = false;
      }

      if (!valid) {
        e.preventDefault();
        return;
      }

      var btn = form.querySelector('[type="submit"]');
      setButtonLoading(btn, true);
    });
  }

  /* ── helper ── */
  function showError(el, msg) {
    var parent = el.closest('.form-group') || el.parentElement;
    var errEl  = parent && parent.querySelector('.form-error');
    if (errEl) {
      errEl.textContent  = msg;
      errEl.style.display = 'block';
    }
  }

  /* ════════════════════════════════════════
     PASSWORD VISIBILITY TOGGLE
  ════════════════════════════════════════ */
  function initPasswordToggles() {
    document.querySelectorAll('.toggle-password').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var input = btn.previousElementSibling;
        if (!input) return;
        var isText = input.type === 'text';
        input.type = isText ? 'password' : 'text';
        btn.textContent = isText ? '👁️' : '🙈';
      });
    });
  }

  /* ════════════════════════════════════════
     FLASH MESSAGES FROM PHP
  ════════════════════════════════════════ */
  function initFlashMessages() {
    var flashes = document.querySelectorAll('[data-flash]');
    flashes.forEach(function (el) {
      var msg  = el.dataset.flash;
      var type = el.dataset.flashType || 'info';
      if (msg) {
        setTimeout(function () { showToast(msg, type); }, 400);
        el.remove();
      }
    });
  }

  /* ════════════════════════════════════════
     INIT
  ════════════════════════════════════════ */
  document.addEventListener('DOMContentLoaded', function () {
    initImageUpload();
    initLoginForm();
    initRegisterForm();
    initSubmitForm();
    initPasswordToggles();
    initFlashMessages();
  });

  // Expose selectedFiles for form submission hook
  window.getSelectedFiles = function () { return selectedFiles; };
})();
