/**
 * JS版 serialize
 * @作者 Ma Bingyao <andot@ujn.edu.cn>
 * @修改 苏相锟，考虑到在PHP中，对象和数组的模糊问题，一切对象都转成数组
 * @版权 Copyright (C) 2006 Ma Bingyao <andot@ujn.edu.cn> 
 * @主页 https://www.phpok.com
 * @版本 3.0c
 * @授权 This library is free.  You can redistribute it and/or modify it. 
 * @时间 Jun 2, 2006
**/
 
function serialize(o) { 
    var p = 0, sb = [], ht = [], hv = 1; 
    function classname(o) { 
        if (typeof(o) == "undefined" || typeof(o.constructor) == "undefined") return ''; 
        var c = o.constructor.toString(); 
        c = utf16to8(c.substr(0, c.indexOf('(')).replace(/(^\s*function\s*)|(\s*$)/ig, '')); 
        return ((c == '') ? 'Object' : c); 
    }
    function chkLength(strTemp) {
		var i,sum;  
		sum=0;  
		for(i=0;i<strTemp.length;i++){  
			if ((strTemp.charCodeAt(i)>=0) && (strTemp.charCodeAt(i)<=255))  
				sum=sum+1;  
			else  
				sum=sum+3;  
		}  
		return sum;  
	}
	function utf16to8(utf16Str){
		var utf8Arr = [];
		var byteSize = 0;
		for (var i = 0; i < utf16Str.length; i++) {
			//获取字符Unicode码值
			var code = utf16Str.charCodeAt(i);
			//如果码值是1个字节的范围，则直接写入
			if (code >= 0x00 && code <= 0x7f) {
				byteSize += 1;
				utf8Arr.push(code);

				//如果码值是2个字节以上的范围，则按规则进行填充补码转换
			} else if (code >= 0x80 && code <= 0x7ff) {
				byteSize += 2;
				utf8Arr.push((192 | (31 & (code >> 6))));
				utf8Arr.push((128 | (63 & code)))
			} else if ((code >= 0x800 && code <= 0xd7ff)
				|| (code >= 0xe000 && code <= 0xffff)) {
				byteSize += 3;
				utf8Arr.push((224 | (15 & (code >> 12))));
				utf8Arr.push((128 | (63 & (code >> 6))));
				utf8Arr.push((128 | (63 & code)))
			} else if(code >= 0x10000 && code <= 0x10ffff ){
				byteSize += 4;
				utf8Arr.push((240 | (7 & (code >> 18))));
				utf8Arr.push((128 | (63 & (code >> 12))));
				utf8Arr.push((128 | (63 & (code >> 6))));
				utf8Arr.push((128 | (63 & code)))
			}
		}
		return utf8Arr
	}
    function is_int(n) { 
        var s = n.toString(), l = s.length; 
        if (l > 11) return false; 
        for (var i = (s.charAt(0) == '-') ? 1 : 0; i < l; i++) { 
            switch (s.charAt(i)) { 
                case '0': 
                case '1': 
                case '2': 
                case '3': 
                case '4': 
                case '5': 
                case '6': 
                case '7': 
                case '8': 
                case '9': break; 
                default : return false; 
            } 
        } 
        return !(n < -2147483648 || n > 2147483647); 
    } 
    function in_ht(o) { 
        for (k in ht){
	        if (ht[k] === o) return k;
        }
        return false; 
    } 
    function ser_null() { 
        sb[p++] = 'N;'; 
    } 
    function ser_boolean(b) { 
        sb[p++] = (b ? 'b:1;' : 'b:0;'); 
    } 
    function ser_integer(i) { 
        sb[p++] = 'i:' + i + ';'; 
    } 
    function ser_double(d) { 
        if (d == Number.POSITIVE_INFINITY) d = 'INF'; 
        else if (d == Number.NEGATIVE_INFINITY) d = '-INF'; 
        sb[p++] = 'd:' + d + ';'; 
    }
    function ser_string(s) { 
        //var utf8 = utf16to8(s);
        var utf8 = s;//当判断是中文时不进行编码转换
        sb[p++] = 's:' + chkLength(utf8) + ':"'; 
        sb[p++] = utf8; 
        sb[p++] = '";'; 
    } 
    function ser_array(a) { 
        sb[p++] = 'a:'; 
        var lp = p; 
        sb[p++] = 0; 
        sb[p++] = ':{'; 
        for (var k in a) { 
            if (typeof(a[k]) != 'function') { 
                is_int(k) ? ser_integer(k) : ser_string(k); 
                __serialize(a[k]); 
                sb[lp]++; 
            } 
        } 
        sb[p++] = '}'; 
    } 
    function ser_pointref(R) { 
        sb[p++] = "R:" + R + ";"; 
    } 
    function ser_ref(r) { 
        sb[p++] = "r:" + r + ";"; 
    }
    function __serialize(o) {
        if (o == null || o.constructor == Function) { 
            hv++; 
            ser_null(); 
        }
        else switch (o.constructor) { 
            case Boolean: { 
                hv++; 
                ser_boolean(o); 
                break; 
            } 
            case Number: { 
                hv++; 
                is_int(o) ? ser_integer(o) : ser_double(o); 
                break; 
            } 
            case String: { 
                hv++; 
                ser_string(o); 
                break; 
            } 
            case Array: { 
                var r = in_ht(o); 
                if (r) { 
                    ser_pointref(r); 
                } 
                else { 
                    ht[hv++] = o; 
                    ser_array(o); 
                }
                break; 
            } 
            default: { 
                var r = in_ht(o); 
                if (r) { 
                    hv++; 
                    ser_ref(r); 
                } 
                else { 
                    ht[hv++] = o;
                    ser_array(o);
                }
                break; 
            } 
        } 
    } 
    __serialize(o); 
    return sb.join(''); 
}

