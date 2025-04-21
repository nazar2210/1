document.addEventListener('DOMContentLoaded', function() {
    const bookingForm = document.getElementById('bookingForm');
    
    if (bookingForm) {
        bookingForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const form = this;
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            
            // Очистка предыдущих сообщений об ошибке
            const oldError = form.querySelector('.form-error');
            if (oldError) oldError.remove();
            
            // Блокировка кнопки
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <span class="spinner"></span> Отправка...
            `;
            
            try {
                const formData = new FormData(form);
                
                // Добавляем дополнительную информацию
                formData.append('date', document.querySelector('[name="date"]')?.value || '');
                formData.append('time', document.querySelector('[name="time"]')?.value || '');
                
                const response = await fetch('telegram.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Показываем уведомление об успехе
                    showAlert(result.message, 'success');
                    form.reset();
                } else {
                    // Показываем ошибку под формой
                    const errorElement = document.createElement('div');
                    errorElement.className = 'form-error';
                    errorElement.innerHTML = `
                        <i class="fas fa-exclamation-circle"></i> ${result.message}
                    `;
                    form.insertBefore(errorElement, submitBtn);
                    
                    // Прокрутка к ошибке
                    errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            } catch (error) {
                console.error('Ошибка:', error);
                showAlert('Сбой соединения. Позвоните нам: +7 (999) 141-70-17', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });
    }
    
    function showAlert(message, type = 'success') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.innerHTML = `
            <div class="alert-content">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                ${message}
            </div>
            <button class="alert-close">&times;</button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Автоматическое закрытие через 5 секунд
        setTimeout(() => {
            alertDiv.classList.add('fade-out');
            setTimeout(() => alertDiv.remove(), 300);
        }, 5000);
        
        // Закрытие по клику
        alertDiv.querySelector('.alert-close').addEventListener('click', () => {
            alertDiv.classList.add('fade-out');
            setTimeout(() => alertDiv.remove(), 300);
        });
    }
});
