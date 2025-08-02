// Tab switching functionality
function setupAdminTabs() {
  document.querySelectorAll('.admin-nav a').forEach(link => {
    link.addEventListener('click', function(e) {
      // Highlight active tab
      document.querySelectorAll('.admin-nav a').forEach(navLink => {
        navLink.classList.remove('active');
      });
      this.classList.add('active');
    });
  });
}

// Modal functions
function setupUserModals() {
  function openEditModal(userId, username, email, role) {
    // Decode any encoded HTML entities
    const decode = (str) => {
      const txt = document.createElement('textarea');
      txt.innerHTML = str;
      return txt.value;
    };

    document.getElementById('modalUserId').value = userId;
    document.getElementById('modalUsername').value = username;
    document.getElementById('modalEmail').value = email;
    document.getElementById('modalRole').value = role;
    document.getElementById('editModal').style.display = 'flex';
  }

  function closeModal() {
    document.getElementById('editModal').style.display = 'none';
  }

  function resetPassword(userId) {
    if (confirm('Reset password to "temporary123"? This cannot be undone.')) {
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = 'admin.php?tab=users';
      
      const userIdInput = document.createElement('input');
      userIdInput.type = 'hidden';
      userIdInput.name = 'user_id';
      userIdInput.value = userId;
      form.appendChild(userIdInput);
      
      const actionInput = document.createElement('input');
      actionInput.type = 'hidden';
      actionInput.name = 'reset_password';
      actionInput.value = '1';
      form.appendChild(actionInput);
      
      document.body.appendChild(form);
      form.submit();
    }
  }

  // Close modal when clicking outside
  window.onclick = function(event) {
    if (event.target == document.getElementById('editModal')) {
      closeModal();
    }
  }

  // Expose functions to global scope if needed
  window.openEditModal = openEditModal;
  window.closeModal = closeModal;
  window.resetPassword = resetPassword;
}

// Initialize all functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  setupAdminTabs();
  
  // Only setup modals if we're on users tab
  if (document.getElementById('editModal')) {
    setupUserModals();
  }
});