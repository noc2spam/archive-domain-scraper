
(function () {
    var $d = $(document);
    var $w = $(window);
    var $container = $('#container');
    var $input = $container.find('#input');
    var $output = $container.find('#output'); 
    var $button = $container.find('#scrape_button');
    var Log = console.log;
    var LogMessage = function (message, messageType, destroyTime) {
        if (typeof destroyTime == 'undefined') {
            destroyTime = 20000;
        }
        if (typeof messageType == 'undefined') {
            messageType = 'log';
        }
        if($output.val() == 'log window'){
            $output.val(message);
        } else {
           $output.val( $output.val() + "\n"  + message) 
        }
         
    };
    var Placeholder = function () {
        var label = 'enter one domain each line. No http://';
        var placeholderColor = '#CCC';
        var defaultColor = '#000';
        var init = function (forceLabel) {
            if (forceLabel) {
                $input.trigger('blur');
            }
            if ($.trim($input.val()) === '' || $input.val() === label || forceLabel) {
                $input.val(label);
                $input.css({
                    color: placeholderColor
                });
            } else {
                $input.css({
                    color: defaultColor
                });
            }

        };
        $input.blur(function () {
            init();
        });
        $input.focus(function () {
            if ($input.val() === label) {
                $input.val('');
                $input.css({
                    color: defaultColor
                });
            }
        });
        init(true);
    };
    var Job = function (urlList) {
        MAX_URL_COUNT = 1000;
        var MAX_URL_ERROR = "Maximum " + MAX_URL_COUNT + " URLs at a time";
        var urls = [];

        var init = function () {
            $.each(urlList.split(/\n/), function (i, line) {
                if (line) {
                    urls.push(line);
                } else {
                    urls.push("");
                }
            });
            urls = urls.reverse();
            if (urls.length > MAX_URL_COUNT) {
                LogMessage(MAX_URL_ERROR, 'error');
            } else {
                scrape();
            }

        };
        var scrape = function () {
            if (urls.length < 1) {
                Log("All done");
                return;
            }
            var url = $.trim(urls.pop());
            LogMessage("Current URL: " + url);
            $.ajax({
                type: "post",
                dataType : "json",
                url: 'ajax/process.php',
                data: {
                    url: url
                }
            }).always(function (response, status, errorMessage) {
                if (response.success) {
                    LogMessage(response.message);
                    $input.val(response.html);
                } else {
                    LogMessage(response.message, 'error');
                }
                setTimeout(function () {
                    scrape();
                }, 1000);
            });
        };
        init();
    };
    $d.ready(function () {
        $container.css({
            minHeight: window.outerHeight
        });
        Placeholder();
        $button.click(function () {
            Job($input.val());
        });
    });


})();