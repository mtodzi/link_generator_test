$(document).ready(function() {
    // Перехватываем событие перед отправкой формы ActiveForm
    $('#short-link-form').on('beforeSubmit', function() {

        const form = $(this);
        const resultContainer = $('#result');
        const submitButton = form.find('button[type="submit"]');

        // Блокируем кнопку и показываем статус
        submitButton.prop('disabled', true).html('Обработка...');
        resultContainer.html(''); // Очищаем предыдущий результат

        $.ajax({
            url: form.attr('action'),
            type: 'post',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    const resultHtml = `
                        <div class="alert alert-success text-center">
                            <p>Ваша короткая ссылка: <a href="${response.shortUrl}" target="_blank" rel="noopener noreferrer">${response.shortUrl}</a></p>
                            <div class="mt-3">
                                <p class="mb-1">QR-код для ссылки:</p>
                                <img src="${response.qrCodeDataUri}" alt="QR Code for short link" style="width: 200px; height: 200px;">
                            </div>
                        </div>
                    `;
                    resultContainer.html(resultHtml);
                } else {
                    // Yii ActiveForm сам покажет ошибки валидации под полем
                    form.yiiActiveForm('updateMessages', response.errors, true);
                }
            },
            error: function() {
                resultContainer.html('<div class="alert alert-danger">Произошла ошибка. Попробуйте еще раз.</div>');
            }
        }).always(function() {
            // В любом случае возвращаем кнопку в исходное состояние
            submitButton.prop('disabled', false).html('Сократить');
        });

        return false; // Отменяем стандартную отправку формы
    });
});