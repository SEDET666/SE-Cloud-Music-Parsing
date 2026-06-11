<?php
/**
 * SE云音解析 - API代理
 * 完全基于Python参考代码的eapi加密协议重写
 * 加密算法与Python输出完全一致
 */

// ============ 常量（与Python代码完全对应） ============
define('EAPI_KEY', 'e82ckenh8dichen8');
define('USER_AGENT', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Safari/537.36 Chrome/91.0.4472.164 NeteaseMusicDesktop/2.10.2.200154');
define('REFERER', 'https://music.163.com/');
define('DEFAULT_COOKIE', 'os=pc; appver=; osver=; deviceId=pyncm!');

// ============ Python兼容的JSON编码器 ============

/**
 * 递归构建JSON字符串，格式与Python的json.dumps完全一致
 * Python默认separators=(', ', ': ')，冒号和逗号后有空格
 */
function pyJsonEncode($data) {
    if ($data === null) return 'null';
    if (is_bool($data)) return $data ? 'true' : 'false';
    if (is_int($data)) return (string)$data;
    if (is_float($data)) {
        if (is_infinite($data) || is_nan($data)) return 'null';
        return (string)$data;
    }
    if (is_string($data)) {
        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
    if (is_array($data)) {
        if (empty($data)) return '[]';
        $isList = array_keys($data) === range(0, count($data) - 1);
        if ($isList) {
            $items = array_map('pyJsonEncode', $data);
            return '[' . implode(', ', $items) . ']';
        } else {
            $items = [];
            foreach ($data as $k => $v) {
                $key = json_encode((string)$k, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                $items[] = $key . ': ' . pyJsonEncode($v);
            }
            return '{' . implode(', ', $items) . '}';
        }
    }
    return 'null';
}

// ============ 加密工具（与Python CryptoUtils完全对应） ============

/**
 * eapi参数加密
 * 对应Python: CryptoUtils.encrypt_params
 */
function eapiEncrypt($url, $payload) {
    $urlPath = parse_url($url, PHP_URL_PATH);
    $apiPath = str_replace('/eapi/', '/api/', $urlPath);

    // Python: json.dumps(payload) — 使用兼容编码器
    $payloadJson = pyJsonEncode($payload);

    // Python: f"nobody{url_path}use{json.dumps(payload)}md5forencrypt"
    // Python: CryptoUtils.hash_hex_digest(...)
    $digest = md5("nobody" . $apiPath . "use" . $payloadJson . "md5forencrypt");

    // Python: f"{url_path}-36cd479b6b5-{json.dumps(payload)}-36cd479b6b5-{digest}"
    $params = $apiPath . "-36cd479b6b5-" . $payloadJson . "-36cd479b6b5-" . $digest;

    // Python: AES-128-ECB + PKCS7 padding
    $encrypted = openssl_encrypt($params, 'AES-128-ECB', EAPI_KEY, OPENSSL_RAW_DATA);

    // Python: CryptoUtils.hex_digest(enc) — bin2hex
    return bin2hex($encrypted);
}

/**
 * 图片ID加密算法
 * 对应Python: NeteaseAPI.netease_encrypt_id
 */
function neteaseEncryptId($idStr) {
    $magic = '3go8&$8*3*3h0k(2)2';
    $len = strlen($idStr);
    $magicLen = strlen($magic);
    $result = '';
    for ($i = 0; $i < $len; $i++) {
        $result .= chr(ord($idStr[$i]) ^ ord($magic[$i % $magicLen]));
    }
    $md5 = md5($result, true);
    $base64 = base64_encode($md5);
    return str_replace(['/', '+'], ['_', '-'], $base64);
}

/**
 * 获取封面直链
 * 对应Python: NeteaseAPI.get_pic_url
 */
function getPicUrl($picId, $size = 300) {
    if (!$picId) return '';
    $encId = neteaseEncryptId(strval($picId));
    return "https://p3.music.126.net/{$encId}/{$picId}.jpg?param={$size}y{$size}";
}

// ============ HTTP客户端（与Python HTTPClient完全对应） ============

/**
 * 通用GET请求
 */
function httpGet($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT => USER_AGENT,
        CURLOPT_REFERER => REFERER,
        CURLOPT_HTTPHEADER => ['Cookie: ' . DEFAULT_COOKIE],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) return ['code' => 500, 'msg' => 'cURL: ' . $err];
    if ($code !== 200) return ['code' => $code, 'msg' => 'HTTP ' . $code];
    $r = json_decode($resp, true);
    return $r ?: ['code' => 500, 'msg' => 'JSON解析失败'];
}

/**
 * 通用POST请求
 * 对应Python: HTTPClient.post_request
 */
function httpPost($url, $data, $cookies = []) {
    $cookieStr = DEFAULT_COOKIE;
    if (!empty($cookies)) {
        $parts = [];
        foreach ($cookies as $k => $v) $parts[] = "$k=$v";
        $cookieStr .= '; ' . implode('; ', $parts);
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT => USER_AGENT,
        CURLOPT_REFERER => REFERER,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => is_array($data) ? http_build_query($data) : $data,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'Cookie: ' . $cookieStr,
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) return ['code' => 500, 'msg' => 'cURL: ' . $err];
    if ($code !== 200) return ['code' => $code, 'msg' => 'HTTP ' . $code];
    $r = json_decode($resp, true);
    return $r ?: ['code' => 500, 'msg' => 'JSON解析失败'];
}

/**
 * eapi加密POST请求
 * 对应Python: 先encrypt_params，再post_request发送params=xxx
 */
function eapiPost($url, $payload, $cookies = []) {
    $params = eapiEncrypt($url, $payload);
    return httpPost($url, ['params' => $params], $cookies);
}

// ============ API业务函数（与Python NeteaseAPI类完全对应） ============

/**
 * VIP歌曲降级解析
 * 手动跟随重定向，每次都带上完整Header，防止被网易云中转拦截到404
 */
/**
 * VIP歌曲降级解析
 * 直接返回网易云官方外链地址，前端audio/a标签自动跟随302重定向
 */
function getVipUrl($id) {
    return "https://music.163.com/song/media/outer/url?id=" . intval($id) . ".mp3";
}

/**
 * 获取歌曲播放URL
 * 优先eapi加密，失败降级VIP接口
 */
function songUrl($id, $level = 'exhigh') {
    $id = intval($id);
    $allowed = ['standard', 'exhigh', 'lossless', 'hires', 'sky', 'jyeffect', 'jymaster', 'dolby'];
    if (!in_array($level, $allowed)) $level = 'exhigh';

    // ===== 第一步：eapi加密请求 =====
    $config = [
        'os' => 'pc',
        'appver' => '',
        'osver' => '',
        'deviceId' => 'pyncm!',
        'requestId' => strval(rand(20000000, 30000000)),
    ];

    $payload = [
        'ids' => [$id],
        'level' => $level,
        'encodeType' => 'flac',
        'header' => pyJsonEncode($config),
    ];

    if ($level === 'sky') {
        $payload['immerseType'] = 'c51';
    }

    $result = eapiPost('https://interface3.music.163.com/eapi/song/enhance/player/url/v1', $payload);

    // eapi成功且有URL，直接返回
    if (isset($result['code']) && $result['code'] === 200 && isset($result['data']) && !empty($result['data'])) {
        $url = $result['data'][0]['url'] ?? '';
        if (!empty($url)) {
            return $result;
        }
    }

    // ===== 第二步：eapi返回url为空，尝试VIP降级接口 =====
    $vipUrl = getVipUrl($id);
    if ($vipUrl) {
        // 判断文件类型
        $ext = pathinfo(parse_url($vipUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'mp3';
        $typeMap = ['mp3' => 'mp3', 'flac' => 'flac', 'wav' => 'wav', 'ogg' => 'ogg', 'm4a' => 'm4a'];
        $type = isset($typeMap[$ext]) ? $typeMap[$ext] : 'mp3';

        return [
            'code' => 200,
            'data' => [[
                'id' => $id,
                'url' => $vipUrl,
                'br' => 320000,
                'size' => 0,
                'md5' => null,
                'code' => 200,
                'expi' => 1200,
                'type' => $type,
                'gain' => 0,
                'peak' => 1.0,
                'fee' => 1,
                'level' => 'exhigh',
                'encodeType' => $type,
                'channelLayout' => null,
                'time' => 0,
            ]],
            '_source' => 'vip_fallback',
        ];
    }

    // ===== 第三步：都失败了 =====
    return ['code' => 200, 'data' => [['id' => $id, 'url' => '', 'code' => 404]]];
}

/**
 * 获取歌曲详情
 * 对应Python: NeteaseAPI.get_song_detail
 */
function detail($id) {
    return httpPost('https://interface3.music.163.com/api/v3/song/detail', [
        'c' => json_encode([['id' => intval($id), 'v' => 0]]),
    ]);
}

/**
 * 获取歌词
 * 对应Python: NeteaseAPI.get_lyric
 */
function lyric($id) {
    return httpPost('https://interface3.music.163.com/api/song/lyric', [
        'id' => intval($id),
        'cp' => 'false',
        'tv' => '0',
        'lv' => '0',
        'rv' => '0',
        'kv' => '0',
        'yv' => '0',
        'ytv' => '0',
        'yrv' => '0',
    ]);
}

/**
 * 搜索音乐
 * 对应Python: NeteaseAPI.search_music
 * 使用 /api/cloudsearch/pc（和Python代码一致）
 */
function search($keyword, $type = 1, $page = 1, $limit = 30) {
    $offset = ($page - 1) * $limit;

    // 方法1: /api/cloudsearch/pc（Python代码用的就是这个）
    $result = httpPost('https://music.163.com/api/cloudsearch/pc', [
        's' => $keyword,
        'type' => intval($type),
        'limit' => $limit,
        'offset' => $offset,
    ]);
    if (isset($result['code']) && $result['code'] === 200 && isset($result['result'])) {
        return $result;
    }

    // 方法2: eapi加密搜索降级
    $result = eapiPost('https://interface3.music.163.com/eapi/cloudsearch/pc', [
        's' => $keyword,
        'type' => intval($type),
        'limit' => $limit,
        'offset' => $offset,
    ]);
    if (isset($result['code']) && $result['code'] === 200 && isset($result['result'])) {
        return $result;
    }

    return $result;
}

/**
 * 获取歌单详情
 * 对应Python: NeteaseAPI.get_playlist_detail
 */
function playlistDetail($id) {
    $id = intval($id);

    // 第一步：获取歌单基本信息和trackIds
    $result = httpPost('https://music.163.com/api/v6/playlist/detail', [
        'id' => $id,
    ]);

    if (!isset($result['code']) || $result['code'] !== 200 || !isset($result['playlist'])) {
        return $result;
    }

    $pl = &$result['playlist'];

    // 第二步：如果tracks不完整，用trackIds分批获取详情（和Python逻辑一致）
    $trackIds = [];
    if (isset($pl['trackIds']) && is_array($pl['trackIds'])) {
        foreach ($pl['trackIds'] as $t) {
            $trackIds[] = is_array($t) ? intval($t['id']) : intval($t);
        }
    }

    $tracks = isset($pl['tracks']) ? $pl['tracks'] : [];
    if (count($tracks) < count($trackIds) && !empty($trackIds)) {
        $trackIds = array_slice($trackIds, 0, 1000);
        $allSongs = [];
        $batches = array_chunk($trackIds, 100);
        foreach ($batches as $batch) {
            $batchData = [];
            foreach ($batch as $tid) {
                $batchData[] = ['id' => $tid, 'v' => 0];
            }
            $songResult = httpPost('https://interface3.music.163.com/api/v3/song/detail', [
                'c' => json_encode($batchData),
            ]);
            if (isset($songResult['songs'])) {
                $allSongs = array_merge($allSongs, $songResult['songs']);
            }
        }
        $pl['tracks'] = $allSongs;
    }

    return $result;
}

/**
 * 获取专辑详情
 * 对应Python: NeteaseAPI.get_album_detail
 */
function albumDetail($id) {
    $id = intval($id);
    $result = httpGet('https://music.163.com/api/v1/album/' . $id);

    if (!isset($result['code']) || $result['code'] !== 200 || !isset($result['album'])) {
        return $result;
    }

    // 补专辑封面（Python: self.get_pic_url(album.get("pic"))）
    if (isset($result['album']['pic']) && empty($result['album']['picUrl'])) {
        $result['album']['picUrl'] = getPicUrl($result['album']['pic'], 500);
    }

    // 补歌曲封面（Python: self.get_pic_url(song['al'].get('pic'))）
    if (isset($result['songs'])) {
        foreach ($result['songs'] as &$song) {
            if (isset($song['al']['pic']) && empty($song['al']['picUrl'])) {
                $song['al']['picUrl'] = getPicUrl($song['al']['pic'], 300);
            }
        }
        unset($song);
    }

    return $result;
}

/**
 * 榜单
 */
function toplist() {
    return httpGet('https://music.163.com/api/toplist/detail');
}

// ============ 路由 ============

 $action = $_GET['action'] ?? '';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

switch ($action) {
    case 'search':
        $keyword = trim($_GET['keyword'] ?? '');
        $type = intval($_GET['type'] ?? 1);
        $page = max(1, intval($_GET['page'] ?? 1));
        if (empty($keyword)) { echo json_encode(['code' => 400, 'msg' => '请输入关键词'], JSON_UNESCAPED_UNICODE); exit; }
        echo json_encode(search($keyword, $type, $page), JSON_UNESCAPED_UNICODE);
        break;

    case 'detail':
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) { echo json_encode(['code' => 400, 'msg' => '无效ID'], JSON_UNESCAPED_UNICODE); exit; }
        echo json_encode(detail($id), JSON_UNESCAPED_UNICODE);
        break;

    case 'url':
        $id = intval($_GET['id'] ?? 0);
        $level = $_GET['level'] ?? 'exhigh';
        if ($id <= 0) { echo json_encode(['code' => 400, 'msg' => '无效ID'], JSON_UNESCAPED_UNICODE); exit; }
        echo json_encode(songUrl($id, $level), JSON_UNESCAPED_UNICODE);
        break;

    case 'lyric':
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) { echo json_encode(['code' => 400, 'msg' => '无效ID'], JSON_UNESCAPED_UNICODE); exit; }
        echo json_encode(lyric($id), JSON_UNESCAPED_UNICODE);
        break;

    case 'album':
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) { echo json_encode(['code' => 400, 'msg' => '无效ID'], JSON_UNESCAPED_UNICODE); exit; }
        echo json_encode(albumDetail($id), JSON_UNESCAPED_UNICODE);
        break;

    case 'playlist':
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) { echo json_encode(['code' => 400, 'msg' => '无效ID'], JSON_UNESCAPED_UNICODE); exit; }
        echo json_encode(playlistDetail($id), JSON_UNESCAPED_UNICODE);
        break;

    case 'toplist':
        echo json_encode(toplist(), JSON_UNESCAPED_UNICODE);
        break;

    default:
        echo json_encode(['code' => 200, 'msg' => 'SE云音解析API', 'actions' => ['search','detail','url','lyric','album','playlist','toplist']], JSON_UNESCAPED_UNICODE);
}