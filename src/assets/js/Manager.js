document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.querySelector('.toggle-show-token');
    const inputToken = document.querySelector('.input-token');

    if (inputToken) {
        inputToken.addEventListener('click', function () {
            inputToken.select();
            inputToken.setSelectionRange(0, 99999);

            navigator.clipboard.writeText(inputToken.value)
                .then(() => {
                    alert('Text copied to clipboard!');
                })
        });

        toggleBtn.addEventListener('click', function () {
            if (inputToken.type === 'password') {
                inputToken.type = 'text';
                toggleBtn.innerHTML = '<i class="icon-base ti ti-eye"></i>';
            } else {
                inputToken.type = 'password';
                toggleBtn.innerHTML = '<i class="icon-base ti ti-eye-off"></i>';
            }
        });
    }

    $("#regenerate").on("click", function () {
        $.ajax({
            url: `${window.baseApi}/regenerate`,
            type: 'GET',
            success: function (response) {
                $("input[name=token]").val(response.data.token)
            },
            error: function () {
                alert('An error occurred while regenerating the token.');
            }
        });
    })

    $("select[name=role]").on('change', function () {
        let value = $(this).val();

        if (value === 'admin') {
            $("#permissions").addClass('d-none')
        } else {
            $("#permissions").removeClass('d-none')
        }
    })
});
