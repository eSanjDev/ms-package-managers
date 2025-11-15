document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.querySelector('.toggle-show-token');
    const inputToken = document.querySelector('input[name=token]');

    if (inputToken) {
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

    const refreshBtn = document.getElementById('regenerate');
    refreshBtn.addEventListener('click', function () {
        const length = 16;
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        const array = new Uint32Array(length);
        window.crypto.getRandomValues(array);

        let result = '';
        array.forEach(num => {
            result += chars.charAt(num % chars.length);
        });
        inputToken.value = result
    })


    $("select[name=role]").on('change', function () {
        let value = $(this).val();

        if (value === 'admin') {
            $("#permissions").addClass('d-none')
        } else {
            $("#permissions").removeClass('d-none')
        }
    })

    const selectAll = document.querySelector('#selectAll'),
        checkboxList = document.querySelectorAll('[type="checkbox"]');
    if (selectAll) {
        selectAll.addEventListener('change', t => {
            checkboxList.forEach(e => {
                e.checked = t.target.checked;
            });
        });
    }
});
