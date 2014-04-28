/**
 * Created by wangjunlong on 14-4-28.
 */


$(document).ready(function(){
    $('#register').submit(function (event) {
        event.preventDefault();
        var username=$('#user').val();
        var password=$('#password').val();
        if (username == ''
            || password == '') {
            alert('请输入用户名密码');
            return false;
        }
        var groups='';

        $.post(
            OC.filePath('settings','ajax','createuser.php'),
            {
                username:username,
                password:password,
                groups:groups
            },
            function(result){
                console.log(result);
                if (result.status == "error") {
                    alert(result.data['message']);
                } else {
                    alert('注册成功');
                    window.location.href='/index.php';
                }
            },
            'json'
        );
    })
})
