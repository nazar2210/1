document.addEventListener('DOMContentLoaded', function() {
    const bookingForm = document.getElementById('bookingForm');
    
    if (bookingForm) {
        bookingForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Отправка...';
            
            try {
                const response = await fetch('telegram.php', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams(formData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('✅ ' + result.message);
                    this.reset();
                } else {
                    alert('❌ ' + (result.message || 'Ошибка отправки'));
                }
            } catch (error) {
                console.error('Ошибка:', error);
                alert('❌ Произошла ошибка. Пожалуйста, позвоните нам.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Записаться';
            }
        });
    }
});
