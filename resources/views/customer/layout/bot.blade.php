<!-- Plugins JS -->
<script src="{{asset('assets/customer/js/plugins.js')}}"></script>

<!-- Main JS -->
<script src="{{asset('assets/customer/js/main.js')}}"></script>
{{--cnd thư viện sweetalert có function Swal.mixin để hiển thị thông báo--}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js"></script>
<script src="https://code.jquery.com/jquery-3.4.1.js" integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU="
        crossorigin="anonymous"></script>

{{--Đăng ký--}}
<script>
    {{-- giống như csrf ->tạo ra 1 input có value là token và hidden, gg chỉ như vậy   --}}
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $(document).ready(function () {
        // var password_signup = document.getElementById("password_signup").value;
        // password_signup="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$";
        // var confirm_password_signup = document.getElementById("confirm_password_signup").value;

        // alert(password_signup +" vaf" +confirm_password_signup);

        // function validatePassword_signup() {
            // if (password_signup != confirm_password_signup) {
            //     password_signup.setCustomValidity("Password phải đủ 8 ký tự");
            // } else {
            //     confirm_password_signup.setCustomValidity('');
            // }
        // }
        // password_signup.onchange = validatePassword_signup;
        // confirm_password_signup.onkeyup = validatePassword_signup;

        $('#register').click(function (e) {
            e.preventDefault();
            $('.alert-danger').remove();
            $('.alert-success').remove();
            $.ajax({
                type: "post",
                url: "/customer/signup",
                //Lấy thông tin input
                data: {
                    last_name: $("input[name=last_name]").val(),
                    first_name: $("input[name=first_name]").val(),
                    email: $("#signupModalCenter input[name=email]").val(),
                    password: $("#signupModalCenter input[name=password]").val(),
                },
                dataType: "json",
                success: function (data) {
                    if (typeof data.errors !== "undefined") {
                        jQuery.each(data.errors, function (key, value) {
                            $('.notify').show();
                            $('.notify').append('<div class="alert error-signup alert-danger"><p>' + value + '</p></div>');
                        });
                    } else {
                        console.log(data.success);
                        $('.notify').show();
                        $('.notify').append('<div class="alert alert-success"><p>' + data.success + '</p></div>');
                        $("#form-signup")[0].reset();
                    }
                }
            });
        });
    });
</script>


<script type="text/javascript">
    $(document).ready(function () {
            @if(Session:: has('fail'))
            {{--        sweet alert, thư viện hổ trợ hiển thị mockup    --}}
        const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        Toast.fire({
            type: 'error',
            title: '{{ Session:: get('fail') }}'
        });
            @endif
            @if(Session:: has('success'))
        const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        Toast.fire({
            type: 'success',
            title: '{{ Session:: get('success') }}'
        });
        @endif
        @if(Session:: has('login'))
        Swal.fire({
            type: 'success',
            title: '{{ Session:: get('login') }}',
            showConfirmButton: false,
        });
            @endif

            @if(Session:: has('wrong'))
        const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        Toast.fire({
            type: 'wrong',
            title: '{{ Session:: get('wrong') }}'
        });
        @endif

            @if(Session:: has('forgotsuccess'))
        const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        Toast.fire({
            type: 'forgotsuccess',
            title: '{{ Session:: get('forgotsuccess') }}'
        });
        @endif

        {{--            @if(Session:: has('exist'))--}}
        {{--        const Toast = Swal.mixin({--}}
        {{--                toast: true,--}}
        {{--                position: 'top-end',--}}
        {{--                showConfirmButton: false,--}}
        {{--                timer: 3000--}}
        {{--            });--}}
        {{--        Toast.fire({--}}
        {{--            type: 'wrong',--}}
        {{--            title: '{{ Session:: get('exist') }}'--}}
        {{--        });--}}
        {{--        @endif--}}

    });
</script>


<script>
    $(document).ready(function () {
        var password = document.getElementById("password")
            , confirm_password = document.getElementById("confirm_password");
        
        {{--Kiểm tra password có trùng với confirm_password--}}
        function validatePassword() {
            if (password.value != confirm_password.value) {
                confirm_password.setCustomValidity("Passwords Don't Match");
            } else {
                confirm_password.setCustomValidity('');
            }
        }

        password.onchange = validatePassword;
        confirm_password.onkeyup = validatePassword;

        $('#buttonsubmit').click(function (e) {
            e.preventDefault();
            var email = $('.emailSendtoken').val();
            $.ajax(
                {
                    url: '/customer/resetpassword',
                    type: "post",
                    datatype: "json",
                    data: {
                        'email': email
                    },

                }).done(function (data) {
                console.log(data);
            }).fail(function (jqXHR, ajaxOptions, thrownError) {
                alert('Vui lòng kiểm tra lại thông tin email');

            });
        })
    });



</script>
