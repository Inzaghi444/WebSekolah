// ============================================
// SISTEM SEKOLAH - JAVASCRIPT FUNCTIONS
// ============================================

// Smooth Scroll untuk anchor links
document.addEventListener('DOMContentLoaded', function() {
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    
    anchorLinks.forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            
            if (targetId === '#') return;
            
            const target = document.querySelector(targetId);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

// Form Validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.style.borderColor = '#ef4444';
            input.style.borderWidth = '2px';
        } else {
            input.style.borderColor = '#e5e7eb';
            input.style.borderWidth = '2px';
        }
    });
    
    if (!isValid) {
        alert('⚠ Mohon lengkapi semua field yang wajib diisi!');
    }
    
    return isValid;
}

// Image Preview sebelum upload
function previewImage(input, targetId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const target = document.getElementById(targetId);
            if (target) {
                if (target.tagName === 'IMG') {
                    target.src = e.target.result;
                    target.style.display = 'block';
                } else {
                    target.style.backgroundImage = `url(${e.target.result})`;
                }
            }
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Auto-hide alerts setelah 5 detik
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.3s ease';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
});

// Confirm Delete dengan custom message
function confirmDelete(message) {
    if (!message) {
        message = 'Apakah Anda yakin ingin menghapus data ini?';
    }
    return confirm('⚠ ' + message);
}

// Print Page
function printPage() {
    window.print();
}

// Toggle Mobile Menu
function toggleMobileMenu() {
    const nav = document.querySelector('.nav');
    if (nav) {
        nav.classList.toggle('mobile-active');
    }
}

// Format Number dengan pemisah ribuan
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Validate File Upload
function validateFileUpload(input, maxSizeMB) {
    if (!maxSizeMB) maxSizeMB = 5;
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const fileSize = file.size / 1024 / 1024; // in MB
        
        // Check file size
        if (fileSize > maxSizeMB) {
            alert(`❌ Ukuran file terlalu besar! Maksimal ${maxSizeMB}MB`);
            input.value = '';
            return false;
        }
        
        // Check file type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            alert('❌ Format file tidak didukung! Hanya JPG, PNG, dan GIF yang diperbolehkan.');
            input.value = '';
            return false;
        }
        
        return true;
    }
    return false;
}

// Auto-resize textarea
document.addEventListener('DOMContentLoaded', function() {
    const textareas = document.querySelectorAll('textarea');
    
    textareas.forEach(textarea => {
        // Set initial height
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
        
        // Auto resize on input
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    });
});

// Loading Indicator
function showLoading(message) {
    if (!message) message = 'Loading...';
    
    // Remove existing loader
    hideLoading();
    
    const loader = document.createElement('div');
    loader.id = 'loading-overlay';
    loader.innerHTML = `
        <div style="text-align: center;">
            <div class="spinner"></div>
            <p style="color: white; margin-top: 1rem; font-size: 1.1rem;">${message}</p>
        </div>
    `;
    loader.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    `;
    
    document.body.appendChild(loader);
}

function hideLoading() {
    const loader = document.getElementById('loading-overlay');
    if (loader) {
        loader.remove();
    }
}

// Export Table to CSV
function exportTableToCSV(tableId, filename) {
    if (!filename) filename = 'data.csv';
    
    const table = document.getElementById(tableId);
    if (!table) {
        alert('❌ Tabel tidak ditemukan!');
        return;
    }
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const csvRow = [];
        
        cols.forEach(col => {
            let text = col.innerText;
            // Escape quotes and commas
            text = text.replace(/"/g, '""');
            csvRow.push(`"${text}"`);
        });
        
        csv.push(csvRow.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    link.click();
    window.URL.revokeObjectURL(url);
}

// Countdown Timer
function countdown(elementId, seconds, callback) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    let remaining = seconds;
    
    const interval = setInterval(() => {
        remaining--;
        element.textContent = remaining;
        
        if (remaining <= 0) {
            clearInterval(interval);
            if (callback) callback();
        }
    }, 1000);
}

// Copy to Clipboard
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            alert('✓ Teks berhasil disalin!');
        }).catch(() => {
            alert('❌ Gagal menyalin teks!');
        });
    } else {
        // Fallback for older browsers
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        alert('✓ Teks berhasil disalin!');
    }
}

// Confirm Action dengan Promise
function confirmAction(message) {
    return new Promise((resolve) => {
        const result = confirm(message);
        resolve(result);
    });
}

// Format Rupiah
function formatRupiah(angka) {
    const number = parseInt(angka);
    if (isNaN(number)) return 'Rp 0';
    
    const formatted = number.toLocaleString('id-ID');
    return 'Rp ' + formatted;
}

// Debounce function untuk search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Table Search Function
function searchTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    
    if (!input || !table) return;
    
    input.addEventListener('keyup', debounce(function() {
        const filter = this.value.toUpperCase();
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent || row.innerText;
            if (text.toUpperCase().indexOf(filter) > -1) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }, 300));
}

// Check if element is in viewport
function isInViewport(element) {
    const rect = element.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

// Lazy load images
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
});

// Back to top button
document.addEventListener('DOMContentLoaded', function() {
    // Create back to top button
    const backToTop = document.createElement('button');
    backToTop.innerHTML = '↑';
    backToTop.className = 'back-to-top';
    backToTop.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        display: none;
        z-index: 1000;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        transition: all 0.3s;
    `;
    
    document.body.appendChild(backToTop);
    
    // Show/hide button on scroll
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            backToTop.style.display = 'block';
        } else {
            backToTop.style.display = 'none';
        }
    });
    
    // Scroll to top on click
    backToTop.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});

// Console log for debugging
console.log('✓ Sistem Sekolah - JavaScript Loaded Successfully');
console.log('Version: 1.0.0');
console.log('© 2024 - Sistem Manajemen Sekolah');