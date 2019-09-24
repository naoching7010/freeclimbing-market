$(function () {
    // ヘッダー・slidedownメニュー
    $jsSlideDownMenu = $('.js-slidedown-menu li');
    $jsSlideDownMenu.hover(
        function(){
            $('.js-slidedown-sub:not(:animated)', this).slideDown();
        },
        function(){
            $('.js-slidedown-sub', this).slideUp();
        }
    );
    //イメージスライダー
    var slider = (function(){
        var currentSlideNum = 1;
        var $jsSliderContainer = $('.js-slider-container');
        var slideItemNum = $('.js-slide-item').length;
        var slideItemWidth = $('.js-slide-item').innerWidth();
        var jsImgSliderWidth = slideItemWidth * slideItemNum;
        var DURATION = 500;

        return {
            slidePrev : function(){
                if(currentSlideNum !== 1){
                    $jsSliderContainer.animate({left: '+=' + slideItemWidth + 'px'}, DURATION);
                    currentSlideNum--;
                    $('.js-show-currentNum').eq(currentSlideNum - 1).css({opacity : '1'});
                    $('.js-show-currentNum').eq(currentSlideNum).css({opacity : '0.7'});
                }

            },
            slideNext : function(){
                if(currentSlideNum < slideItemNum){
                    $jsSliderContainer.animate({left: '-=' + slideItemWidth + 'px'}, DURATION);
                    currentSlideNum++;
                    $('.js-show-currentNum').eq(currentSlideNum - 1).css('opacity', '1');
                    $('.js-show-currentNum').eq(currentSlideNum - 2).css('opacity', '0.7');
                }
            },
            init : function(){
                $jsSliderContainer.attr('style', 'width:' + jsImgSliderWidth + 'px');
                var that = this;
                $('.js-slide-prev').on('click', function(){
                    that.slidePrev();
                });
                $('.js-slide-next').on('click', function(){
                    that.slideNext();
                });
                $('.js-show-currentNum').eq(0).css({opacity : '1'});
            }

        }
        //自動スライダーの場合
        /*
        return {
            autoSlider : function(){
                if(currentSlideNum < slideItemNum){
                    $jsSliderContainer.animate({left: '-=' + slideItemWidth + 'px'}, DURATION);
                    currentSlideNum++;
                }else if(currentSlideNum === slideItemNum){
                    $jsSliderContainer.css('left', '+=' + slideItemWidth * (slideItemNum - 1) + 'px');
                    currentSlideNum = 1;
                }
            },
            init : function(){
                $jsSliderContainer.attr('style', 'width:' + jsImgSliderWidth + 'px');
                setInterval(this.autoSlider, 5000);
            }
        }*/
    })();
    slider.init();
    // フッターを最下部に固定
    var $footer = $('#footer');
    if (window.innerHeight > $footer.offset().top + $footer.innerHeight()) {
        $footer.offset({ top: window.innerHeight - $footer.innerHeight(), left: 0 });
    }
    //ドラッグ&ドロップによる画像ライブプレビュー
    var $jsDropContainer = $('.js-dropContainer'),
        $fileInput = $('.file-input')
    //画像をドラッグした時
    $jsDropContainer.on('dragover', function (e) {
        e.stopPropagation();
        e.preventDefault();
        $(this).css('border', '5px dashed #ddd');
    });
    //画像をドロップした時
    $jsDropContainer.on('dragleave', function (e) {
        e.stopPropagation();
        e.preventDefault();
        $(this).css('border', 'none');
    });
    //ファイル読み込み用のinputタグの値が変わった時
    $fileInput.on('change', function (e) {
        $(this).closest('.js-dropContainer').css('border', 'none');

        var file = this.files[0],
            $img = $(this).siblings('.prev-img'),
            fileReader = new FileReader();

        fileReader.onload = function (event) {
            $img.attr('src', event.target.result).show();
        }

        fileReader.readAsDataURL(file);
    });
    //サクセスメッセージ表示
    var $jsFadeMsg = $('.js-fade-msg');
    if ($jsFadeMsg.text().replace(/\s+/g, '').length > 0) {
        $jsFadeMsg.fadeIn(2000);
        setTimeout(function () {
            $jsFadeMsg.fadeOut(2000);
        }, 3000);
    }
    //マウスオーバー時の製品画像切り替え機能
    var $jsSwitchSubImg = $('.js-switch-subimg'),
        $jsSwitchMainImg = $('.js-switch-mainimg');
    $jsSwitchSubImg.on({
        'mouseenter': function () {
            var img = $(this).children('.prev-subimg').attr('src');
            $jsSwitchMainImg.children('.prev-mainimg').attr('src', img);
        }
    });
    //いいね機能
    var $likeProduct = $('#js-like-product') || null;
    likeProductId = $likeProduct.data('productid') || null;
    if (likeProductId !== undefined && likeProductId !== null) {
        $likeProduct.on('click', function () {
            //$this = $(this);
            $.ajax({
                type: 'POST',
                url: 'ajaxLike.php',
                dataType: 'json',
                data: { 'productId': likeProductId }
            }).done(function (data) {
                console.log('success');
                console.log(data);
                if (data) {
                    if (data.loginFlg) { //ログインしていて、いいねした商品のDB登録ができた場合の処理
                        $this = $('.js-like-animation');
                        $this.toggleClass('active');
                        $('.js-like-animation2').toggleClass('active');
                    } else { //ログインしていなかった場合の処理 ログインページへ遷移する
                        location.href = 'login.php';
                    }
                }
            }).fail(function (msg) {
                console.log('err');
            });
        });
    }
    //マイページのテーブルクリックで掲示板へ遷移
    $('.js-move-bord').on('click', function () {
        location.href = $(this).data('href');
    });
    //テキストエリアの高さを自動調節
    var $autoResize = $('.auto-resize');
    $autoResize.on('change keyup keydown paste cut', function () {
        if ($(this).outerHeight() > this.scrollHeight) {
            $(this).height(1);
        }
        while ($(this).outerHeight() < this.scrollHeight) {
            $(this).height($(this).height() + 1);
        }
    });
    //文字数カウンター
    $jsCounter = $('.js-counter');
    $jsCounter.on('keyup', function () {
        var count = $(this).val().length;

        $('.js-show-counter').text(count);
    });
    //入力必須項目がすべて入力されるまで送信ボタンを無効化する
    var $jsFormRequired = $('.js-form-required');
    $jsFormRequired.on('keyup', function () {
        var requiredFlag = true;
        //指定した入力フォームが一つでも空ならば変数にfalseを代入する
        $jsFormRequired.each(function (e) {
            if (!$jsFormRequired.eq(e).val()) {
                requiredFlag = false;
            }
        });
        //必須項目が全て入力されていればdisabled属性を解除する
        if (requiredFlag) {
            $('.js-disabled-submit').prop('disabled', false);
        } else {
            $('.js-disabled-submit').prop('disabled', true);
        }

    });
    //退会時確認用アラート
    var jsShowAlert = $('.js-show-alert');
    jsShowAlert.on('click', function () {
        var flag = confirm('本当に退会してよろしいですか？');
        return flag;
    });
});