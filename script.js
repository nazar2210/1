document.getElementById('bookingForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const form = this;
    const submitBtn = form.querySelector('button[type="submit"]');
    const errorMsg = document.createElement('div');
    errorMsg.className = 'error-message';
    
    // Очистка предыдущих ошибок
    document.querySelectorAll('.error-message').forEach(el => el.remove());
    
    // Блокировка кнопки
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner"></span> Отправка...';
    
    try {
        const formData = new FormData(form);
        const response = await fetch('telegram.php', {
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            form.reset();
        } else {
            errorMsg.textContent = result.message || 'Произошла ошибка';
            errorMsg.style.color = 'red';
            form.insertBefore(errorMsg, submitBtn);
        }
    } catch (error) {
        console.error('Ошибка:', error);
        errorMsg.textContent = 'Сбой соединения. Позвоните нам: +7 (999) 141-70-17';
        errorMsg.style.color = 'red';
        form.insertBefore(errorMsg, submitBtn);
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Записаться';
    }
});
