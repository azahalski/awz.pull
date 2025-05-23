(function() {
    'use strict';

    if (!window.AwzBase64) {
        window.AwzBase64 = {
            _keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
            //метод для кодировки в base64 на javascript
            encode : function (input) {
                var output = "";
                var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
                var i = 0
                input = Base64._utf8_encode(input);
                while (i < input.length) {
                    chr1 = input.charCodeAt(i++);
                    chr2 = input.charCodeAt(i++);
                    chr3 = input.charCodeAt(i++);
                    enc1 = chr1 >> 2;
                    enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
                    enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
                    enc4 = chr3 & 63;
                    if( isNaN(chr2) ) {
                        enc3 = enc4 = 64;
                    }else if( isNaN(chr3) ){
                        enc4 = 64;
                    }
                    output = output +
                        this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
                        this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);
                }
                return output;
            },

            //метод для раскодировки из base64
            decode : function (input) {
                var output = "";
                var chr1, chr2, chr3;
                var enc1, enc2, enc3, enc4;
                var i = 0;
                input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
                while (i < input.length) {
                    enc1 = this._keyStr.indexOf(input.charAt(i++));
                    enc2 = this._keyStr.indexOf(input.charAt(i++));
                    enc3 = this._keyStr.indexOf(input.charAt(i++));
                    enc4 = this._keyStr.indexOf(input.charAt(i++));
                    chr1 = (enc1 << 2) | (enc2 >> 4);
                    chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
                    chr3 = ((enc3 & 3) << 6) | enc4;
                    output = output + String.fromCharCode(chr1);
                    if( enc3 != 64 ){
                        output = output + String.fromCharCode(chr2);
                    }
                    if( enc4 != 64 ) {
                        output = output + String.fromCharCode(chr3);
                    }
                }
                output = Base64._utf8_decode(output);
                return output;
            },
            // метод для кодировки в utf8
            _utf8_encode : function (string) {
                string = string.replace(/\r\n/g,"\n");
                var utftext = "";
                for (var n = 0; n < string.length; n++) {
                    var c = string.charCodeAt(n);
                    if( c < 128 ){
                        utftext += String.fromCharCode(c);
                    }else if( (c > 127) && (c < 2048) ){
                        utftext += String.fromCharCode((c >> 6) | 192);
                        utftext += String.fromCharCode((c & 63) | 128);
                    }else {
                        utftext += String.fromCharCode((c >> 12) | 224);
                        utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                        utftext += String.fromCharCode((c & 63) | 128);
                    }
                }
                return utftext;

            },

            //метод для раскодировки из urf8
            _utf8_decode : function (utftext) {
                var string = "";
                var i = 0;
                var c1, c2, c3;
                var c = c1 = c2 = 0;
                while( i < utftext.length ){
                    c = utftext.charCodeAt(i);
                    if (c < 128) {
                        string += String.fromCharCode(c);
                        i++;
                    }else if( (c > 191) && (c < 224) ) {
                        c2 = utftext.charCodeAt(i+1);
                        string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                        i += 2;
                    }else {
                        c2 = utftext.charCodeAt(i+1);
                        c3 = utftext.charCodeAt(i+2);
                        string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                        i += 3;
                    }
                }
                return string;
            }
        };
    }

    if (!!window.AwzPullClientComponent) {
        return;
    }

    window.AwzPullClientComponent = function(options) {
        if(typeof options !== 'object') options = {};
        this.debug = !!options.debug ? true : false;
        if(!options.hasOwnProperty('url')) {
            throw new Error('options.url is required');
        }
        if(!window.AwzPullClientComponent_Instances){
            window.AwzPullClientComponent_Instances = [];
        }
        window.AwzPullClientComponent_Instances.push(this);
        this.init(options);
    };

    window.AwzPullClientComponent.prototype = {
        init: function (options) {
            this.socket = new ReconnectingWebSocket(
                options.url,
                null,
                {}
            );
            this.socket.addEventListener('message', function(data){
                var pattern = /#!NGINXNMS!#(.*?)#!NGINXNME!#/gi;
                var match;
                while (match = pattern.exec(data.data)){
                    var decoder = new TextDecoder();
                    var msg = JSON.parse(match[1]);
                    try{
                        var tmp = atob(msg.text);
                        msg.text = tmp;
                    }catch (e) {
                        msg.text = window.AwzBase64.decode(msg.text);
                    }

                    msg.message = JSON.parse(msg.text);
                    delete msg.text;
                    BX.onCustomEvent(this, 'awz.pull.onmessage', [msg]);
                }
            });
        }
    }

})(window);