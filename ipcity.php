<?
/*
函数名称：ipCity
参数说明：$userip——用户IP地址
函数功能：PHP通过IP地址判断用户所在城市
author:lee
contact:xpsem2010@gmail.com
*/
function ipCity($userip) {
    //IP数据库路径，这里用的是QQ IP数据库 20110405 纯真版
    $dat_path = 'qqwry.dat';

    //判断IP地址是否有效
    if(!ereg("^([0-9]{1,3}.){3}[0-9]{1,3}$", $userip)){
        return 'IP Address Invalid';
    }

    //打开IP数据库
    if(!$fd = @fopen($dat_path, 'rb')){
        return 'IP data file not exists or access denied';
    }

    //explode函数分解IP地址，运算得出整数形结果
    $userip = explode('.', $userip);
    $useripNum = $userip[0] * 16777216 + $userip[1] * 65536 + $userip[2] * 256 + $userip[3];

    //获取IP地址索引开始和结束位置
    $DataBegin = fread($fd, 4);
    $DataEnd = fread($fd, 4);
    $useripbegin = implode('', unpack('L', $DataBegin));
    if($useripbegin < 0) $useripbegin += pow(2, 32);
    $useripend = implode('', unpack('L', $DataEnd));
    if($useripend < 0) $useripend += pow(2, 32);
    $useripAllNum = ($useripend - $useripbegin) / 7 + 1;

    $BeginNum = 0;
    $EndNum = $useripAllNum;

    //使用二分查找法从索引记录中搜索匹配的IP地址记录
    while($userip1num>$useripNum || $userip2num<$useripNum) {
        $Middle= intval(($EndNum + $BeginNum) / 2);

        //偏移指针到索引位置读取4个字节
        fseek($fd, $useripbegin + 7 * $Middle);
        $useripData1 = fread($fd, 4);
        if(strlen($useripData1) < 4) {
            fclose($fd);
            return 'File Error';
        }
        //提取出来的数据转换成长整形，如果数据是负数则加上2的32次幂
        $userip1num = implode('', unpack('L', $useripData1));
        if($userip1num < 0) $userip1num += pow(2, 32);

        //提取的长整型数大于我们IP地址则修改结束位置进行下一次循环
        if($userip1num > $useripNum) {
            $EndNum = $Middle;
            continue;
        }

        //取完上一个索引后取下一个索引
        $DataSeek = fread($fd, 3);
        if(strlen($DataSeek) < 3) {
            fclose($fd);
            return 'File Error';
        }
        $DataSeek = implode('', unpack('L', $DataSeek.chr(0)));
        fseek($fd, $DataSeek);
        $useripData2 = fread($fd, 4);
        if(strlen($useripData2) < 4) {
            fclose($fd);
            return 'File Error';
        }
        $userip2num = implode('', unpack('L', $useripData2));
        if($userip2num < 0) $userip2num += pow(2, 32);

        //找不到IP地址对应城市
        if($userip2num < $useripNum) {
            if($Middle == $BeginNum) {
                fclose($fd);
                return 'No Data';
            }
            $BeginNum = $Middle;
        }
    }

    $useripFlag = fread($fd, 1);
    if($useripFlag == chr(1)) {
        $useripSeek = fread($fd, 3);
        if(strlen($useripSeek) < 3) {
            fclose($fd);
            return 'System Error';
        }
        $useripSeek = implode('', unpack('L', $useripSeek.chr(0)));
        fseek($fd, $useripSeek);
        $useripFlag = fread($fd, 1);
    }

    if($useripFlag == chr(2)) {
        $AddrSeek = fread($fd, 3);
        if(strlen($AddrSeek) < 3) {
            fclose($fd);
            return 'System Error';
        }
        $useripFlag = fread($fd, 1);
        if($useripFlag == chr(2)) {
            $AddrSeek2 = fread($fd, 3);
            if(strlen($AddrSeek2) < 3) {
                fclose($fd);
                return 'System Error';
            }
            $AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
            fseek($fd, $AddrSeek2);
        } else {
            fseek($fd, -1, SEEK_CUR);
        }

        while(($char = fread($fd, 1)) != chr(0))
            $useripAddr2 .= $char;

        $AddrSeek = implode('', unpack('L', $AddrSeek.chr(0)));
        fseek($fd, $AddrSeek);

        while(($char = fread($fd, 1)) != chr(0))
            $useripAddr1 .= $char;
    } else {
        fseek($fd, -1, SEEK_CUR);
        while(($char = fread($fd, 1)) != chr(0))
            $useripAddr1 .= $char;

        $useripFlag = fread($fd, 1);
        if($useripFlag == chr(2)) {
            $AddrSeek2 = fread($fd, 3);
            if(strlen($AddrSeek2) < 3) {
                fclose($fd);
                return 'System Error';
            }
            $AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
            fseek($fd, $AddrSeek2);
        } else {
            fseek($fd, -1, SEEK_CUR);
        }
        while(($char = fread($fd, 1)) != chr(0)){
            $useripAddr2 .= $char;
        }
    }
    fclose($fd);

    //返回IP地址对应的城市结果
    if(preg_match('/http/i', $useripAddr2)) {
        $useripAddr2 = '';
    }
    $useripaddr = "$useripAddr1 $useripAddr2";
    $useripaddr = preg_replace('/CZ88.Net/is', '', $useripaddr);
    $useripaddr = preg_replace('/^s*/is', '', $useripaddr);
    $useripaddr = preg_replace('/s*$/is', '', $useripaddr);
    if(preg_match('/http/i', $useripaddr) || $useripaddr == '') {
        $useripaddr = 'No Data';
    }

    return $useripaddr;
}
?>
