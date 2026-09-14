document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('logoutModal')) return;
    const modal = document.createElement('div');
    modal.id = 'logoutModal';
    modal.className = 'logout-modal';
    modal.innerHTML = `
        <div class="logout-modal-content">
            <h2>Confirm Logout</h2>
            <p>Are you sure you want to logout? You will be redirected to the homepage.</p>
            <div class="logout-modal-actions">
                <button class="btn-logout-confirm" id="confirmLogout">Yes, Logout</button>
                <button class="btn-logout-cancel" id="cancelLogout">Cancel</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    const style = document.createElement('style');
    style.textContent = `
        .logout-modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:99999; align-items:center; justify-content:center; }
        .logout-modal.active { display:flex; }
        .logout-modal-content { background:var(--white); border-radius:20px; padding:35px 40px; max-width:400px; width:90%; text-align:center; box-shadow:0 20px 60px rgba(0,0,0,0.3); animation:modalSlideIn 0.3s ease; }
        @keyframes modalSlideIn { from { transform:scale(0.9) translateY(20px); opacity:0; } to { transform:scale(1) translateY(0); opacity:1; } }
        .logout-modal-content h2 { font-family:"Poppins",sans-serif; font-weight:700; font-size:22px; color:var(--primary-navyblue); margin-bottom:8px; }
        .logout-modal-content p { font-family:"Poppins",sans-serif; font-size:14px; color:var(--gray); margin-bottom:25px; }
        .logout-modal-actions { display:flex; gap:12px; justify-content:center; }
        .btn-logout-confirm { padding:12px 30px; background:#c62828; color:white; border:none; border-radius:10px; font-family:"Poppins",sans-serif; font-size:14px; font-weight:600; cursor:pointer; transition:all 0.3s ease; }
        .btn-logout-confirm:hover { background:#b71c1c; transform:scale(1.02); }
        .btn-logout-cancel { padding:12px 30px; background:#ddd; color:var(--black); border:none; border-radius:10px; font-family:"Poppins",sans-serif; font-size:14px; font-weight:600; cursor:pointer; transition:all 0.3s ease; }
        .btn-logout-cancel:hover { background:#ccc; }
    `;
    document.head.appendChild(style);
    const logoutModal = document.getElementById('logoutModal');
    const confirmBtn = document.getElementById('confirmLogout');
    const cancelBtn = document.getElementById('cancelLogout');
    document.querySelectorAll('a[href*="logout.php"]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const logoutUrl = this.getAttribute('href');
            logoutModal.classList.add('active');
            document.body.style.overflow = 'hidden';
            confirmBtn.setAttribute('data-url', logoutUrl);
        });
    });
    confirmBtn.addEventListener('click', function() {
        const url = this.getAttribute('data-url') || 'logout.php';
        window.location.href = url;
    });
    cancelBtn.addEventListener('click', function() {
        logoutModal.classList.remove('active');
        document.body.style.overflow = '';
    });
    logoutModal.addEventListener('click', function(e) {
        if (e.target === this) {
            logoutModal.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && logoutModal.classList.contains('active')) {
            logoutModal.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
});