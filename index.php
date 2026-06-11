<?php
/**
 * SE云音解析 - 网易云音乐在线播放与下载
 * 支持歌曲/专辑/歌单/榜单搜索、解析、播放、下载
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SE云音解析 - 网易云音乐在线播放与下载</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.loli.net/css2?family=Outfit:wght@400;600;800&family=Noto+Sans+SC:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --accent: #ff6b35;
            --accent-rgb: 255,107,53;
            --bg: #08080d;
            --bg-secondary: #0f0f18;
            --card: rgba(255,255,255,0.035);
            --card-hover: rgba(255,255,255,0.07);
            --border: rgba(255,255,255,0.06);
            --text: #e8e8ed;
            --text-muted: #6b6b7b;
            --text-dim: #3d3d4d;
            --player-bg: rgba(12,12,20,0.92);
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Noto Sans SC','Outfit',sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
        }
        .bg-blob { position:fixed; border-radius:50%; filter:blur(100px); pointer-events:none; z-index:0; }
        .blob-1 { width:500px;height:500px;background:rgba(var(--accent-rgb),0.1);top:-150px;right:-150px;animation:blobFloat 25s ease-in-out infinite; }
        .blob-2 { width:350px;height:350px;background:rgba(var(--accent-rgb),0.07);bottom:100px;left:-100px;animation:blobFloat 20s ease-in-out infinite reverse; }
        .blob-3 { width:250px;height:250px;background:rgba(var(--accent-rgb),0.05);top:40%;left:50%;animation:blobFloat 30s ease-in-out infinite 5s; }
        @keyframes blobFloat {
            0%,100%{transform:translate(0,0) scale(1)}
            25%{transform:translate(40px,-30px) scale(1.05)}
            50%{transform:translate(-20px,40px) scale(0.95)}
            75%{transform:translate(30px,20px) scale(1.02)}
        }
        ::-webkit-scrollbar{width:6px}
        ::-webkit-scrollbar-track{background:transparent}
        ::-webkit-scrollbar-thumb{background:rgba(255,255,255,0.1);border-radius:3px}
        ::-webkit-scrollbar-thumb:hover{background:rgba(255,255,255,0.2)}

        .site-header{position:sticky;top:0;z-index:40;background:rgba(8,8,13,0.8);backdrop-filter:blur(20px);border-bottom:1px solid var(--border)}
        .logo-text{font-family:'Outfit',sans-serif;font-weight:800;font-size:1.3rem;background:linear-gradient(135deg,var(--accent),rgba(var(--accent-rgb),0.7));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
        .search-box{background:rgba(255,255,255,0.05);border:1px solid var(--border);border-radius:12px;transition:all .3s}
        .search-box:focus-within{border-color:rgba(var(--accent-rgb),0.5);box-shadow:0 0 0 3px rgba(var(--accent-rgb),0.1);background:rgba(255,255,255,0.07)}
        .search-box input{background:transparent;border:none;outline:none;color:var(--text);width:100%;font-size:.95rem}
        .search-box input::placeholder{color:var(--text-dim)}

        .btn-accent{background:var(--accent);color:#fff;border:none;border-radius:10px;padding:10px 20px;font-weight:600;cursor:pointer;transition:all .25s;font-size:.9rem}
        .btn-accent:hover{filter:brightness(1.15);transform:translateY(-1px);box-shadow:0 4px 15px rgba(var(--accent-rgb),0.35)}
        .btn-accent:active{transform:translateY(0)}
        .btn-ghost{background:rgba(255,255,255,0.05);color:var(--text);border:1px solid var(--border);border-radius:10px;padding:8px 16px;cursor:pointer;transition:all .25s;font-size:.85rem}
        .btn-ghost:hover{background:rgba(255,255,255,0.1);border-color:rgba(var(--accent-rgb),0.3)}

        /* 标签页 */
        .tab-bar{display:flex;gap:4px;background:rgba(255,255,255,0.03);border-radius:12px;padding:4px;border:1px solid var(--border)}
        .tab-item{padding:8px 20px;border-radius:9px;cursor:pointer;font-size:.88rem;font-weight:500;color:var(--text-muted);transition:all .25s;white-space:nowrap;user-select:none}
        .tab-item:hover{color:var(--text);background:rgba(255,255,255,0.04)}
        .tab-item.active{background:rgba(var(--accent-rgb),0.15);color:var(--accent);font-weight:600}

        /* 歌曲行 */
        .song-row{display:grid;grid-template-columns:40px 50px 1fr 1fr 60px;align-items:center;gap:12px;padding:10px 16px;border-radius:10px;cursor:pointer;transition:all .2s}
        .song-row:hover{background:var(--card-hover)}
        .song-row.active{background:rgba(var(--accent-rgb),0.1)}
        .song-row .cover-img{width:44px;height:44px;border-radius:6px;object-fit:cover}
        .song-row .song-name{font-weight:500;font-size:.92rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .song-row .song-artist{color:var(--text-muted);font-size:.82rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .song-row .song-album{color:var(--text-dim);font-size:.8rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .song-row .song-duration{color:var(--text-muted);font-size:.8rem;text-align:right}
        .song-row .song-index{color:var(--text-dim);font-size:.85rem;text-align:center}
        .song-row:hover .song-index-num{display:none}
        .song-row .play-icon{display:none;color:var(--accent);text-align:center}
        .song-row:hover .play-icon{display:block}

        /* 卡片网格 */
        .card-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px}
        .card-item{background:var(--card);border:1px solid var(--border);border-radius:12px;overflow:hidden;cursor:pointer;transition:all .25s}
        .card-item:hover{background:var(--card-hover);transform:translateY(-3px);box-shadow:0 8px 30px rgba(0,0,0,0.3);border-color:rgba(var(--accent-rgb),0.2)}
        .card-item img{width:100%;aspect-ratio:1;object-fit:cover}
        .card-item .card-info{padding:12px}
        .card-item .card-title{font-weight:600;font-size:.88rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:4px}
        .card-item .card-sub{color:var(--text-muted);font-size:.78rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

        /* 详情头部 */
        .detail-header{display:flex;gap:24px;margin-bottom:28px;align-items:flex-end}
        .detail-header img{width:200px;height:200px;border-radius:12px;object-fit:cover;box-shadow:0 8px 40px rgba(0,0,0,0.5);flex-shrink:0}
        .detail-header .info h2{font-size:1.5rem;font-weight:800;margin-bottom:8px;line-height:1.3}
        .detail-header .info p{color:var(--text-muted);font-size:.85rem;margin-bottom:4px}

        /* 详情面板（歌曲） */
        .detail-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:50;opacity:0;pointer-events:none;transition:opacity .3s}
        .detail-overlay.open{opacity:1;pointer-events:auto}
        .detail-panel{position:fixed;top:0;right:0;width:480px;max-width:100vw;height:100vh;background:var(--bg-secondary);border-left:1px solid var(--border);z-index:51;transform:translateX(100%);transition:transform .35s cubic-bezier(.4,0,.2,1);overflow-y:auto}
        .detail-panel.open{transform:translateX(0)}
        .detail-cover{width:220px;height:220px;border-radius:12px;object-fit:cover;box-shadow:0 8px 40px rgba(0,0,0,0.4)}

        .quality-tag{display:inline-block;padding:6px 12px;border-radius:8px;font-size:.78rem;cursor:pointer;transition:all .2s;background:rgba(255,255,255,0.05);border:1px solid var(--border);color:var(--text-muted);user-select:none}
        .quality-tag:hover{background:rgba(255,255,255,0.08);color:var(--text)}
        .quality-tag.selected{background:rgba(var(--accent-rgb),0.15);border-color:rgba(var(--accent-rgb),0.5);color:var(--accent);font-weight:600}

        .lyric-line{padding:5px 0;color:var(--text-dim);font-size:.86rem;transition:all .3s;line-height:1.7}
        .lyric-line.active{color:var(--accent);font-weight:600;font-size:.93rem}

        /* 播放器 */
        .player-bar{position:fixed;bottom:0;left:0;right:0;height:76px;background:var(--player-bg);backdrop-filter:blur(24px);border-top:1px solid var(--border);z-index:45;display:flex;align-items:center;padding:0 20px;gap:14px;transform:translateY(100%);transition:transform .4s cubic-bezier(.4,0,.2,1)}
        .player-bar.visible{transform:translateY(0)}
        .player-cover{width:48px;height:48px;border-radius:8px;object-fit:cover;flex-shrink:0}
        .player-info{flex:0 0 170px;overflow:hidden}
        .player-info .title{font-weight:600;font-size:.88rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .player-info .artist{color:var(--text-muted);font-size:.76rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .player-controls{display:flex;align-items:center;gap:14px}
        .ctrl-btn{background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:1.05rem;transition:color .2s,transform .15s;padding:4px}
        .ctrl-btn:hover{color:var(--text);transform:scale(1.1)}
        .ctrl-btn.play-btn{width:38px;height:38px;border-radius:50%;background:var(--accent);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.95rem}
        .ctrl-btn.play-btn:hover{filter:brightness(1.15);transform:scale(1.08)}
        .ctrl-btn.stop-active{color:var(--accent)}
        .progress-wrap{flex:1;display:flex;align-items:center;gap:10px}
        .progress-time{font-size:.73rem;color:var(--text-dim);min-width:36px;text-align:center;font-variant-numeric:tabular-nums}
        .progress-bar{flex:1;height:4px;background:rgba(255,255,255,0.08);border-radius:2px;cursor:pointer;position:relative;transition:height .15s}
        .progress-bar:hover{height:6px}
        .progress-fill{height:100%;background:var(--accent);border-radius:2px;position:relative;transition:width .1s linear}
        .progress-fill::after{content:'';position:absolute;right:-5px;top:50%;transform:translateY(-50%) scale(0);width:10px;height:10px;background:var(--accent);border-radius:50%;transition:transform .15s}
        .progress-bar:hover .progress-fill::after{transform:translateY(-50%) scale(1)}
        .volume-wrap{display:flex;align-items:center;gap:8px}
        .volume-slider{-webkit-appearance:none;width:80px;height:4px;background:rgba(255,255,255,0.1);border-radius:2px;outline:none}
        .volume-slider::-webkit-slider-thumb{-webkit-appearance:none;width:12px;height:12px;background:var(--accent);border-radius:50%;cursor:pointer}

        /* 设置面板 */
        .settings-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:55;opacity:0;pointer-events:none;transition:opacity .3s}
        .settings-overlay.open{opacity:1;pointer-events:auto}
        .settings-panel{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) scale(0.9);width:420px;max-width:92vw;background:var(--bg-secondary);border:1px solid var(--border);border-radius:16px;z-index:56;padding:28px;opacity:0;pointer-events:none;transition:all .3s cubic-bezier(.4,0,.2,1)}
        .settings-panel.open{opacity:1;pointer-events:auto;transform:translate(-50%,-50%) scale(1)}
        .color-preset{width:36px;height:36px;border-radius:10px;cursor:pointer;transition:all .2s;border:2px solid transparent}
        .color-preset:hover{transform:scale(1.12)}
        .color-preset.active{border-color:var(--text);box-shadow:0 0 0 2px var(--bg)}
        .color-picker-wrap{position:relative;width:36px;height:36px}
        .color-picker-wrap input[type="color"]{position:absolute;inset:0;width:100%;height:100%;border:none;padding:0;border-radius:10px;cursor:pointer;background:transparent}
        .color-picker-wrap input[type="color"]::-webkit-color-swatch-wrapper{padding:2px}
        .color-picker-wrap input[type="color"]::-webkit-color-swatch{border-radius:7px;border:none}

        /* Toast */
        .toast-container{position:fixed;top:80px;right:20px;z-index:100;display:flex;flex-direction:column;gap:8px}
        .toast{background:rgba(30,30,45,0.95);backdrop-filter:blur(12px);border:1px solid var(--border);border-radius:10px;padding:12px 18px;font-size:.86rem;color:var(--text);box-shadow:0 8px 30px rgba(0,0,0,0.3);transform:translateX(120%);transition:transform .35s cubic-bezier(.4,0,.2,1);max-width:320px}
        .toast.show{transform:translateX(0)}
        .toast.error{border-left:3px solid #f43f5e}
        .toast.success{border-left:3px solid #10b981}
        .toast.info{border-left:3px solid var(--accent)}

        .spinner{width:32px;height:32px;border:3px solid rgba(var(--accent-rgb),0.2);border-top-color:var(--accent);border-radius:50%;animation:spin .8s linear infinite}
        @keyframes spin{to{transform:rotate(360deg)}}
        .empty-state i{font-size:4rem;color:var(--text-dim);opacity:.4}
        @keyframes fadeInUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
        .fade-in-up{animation:fadeInUp .4s ease both}

        .back-btn{display:inline-flex;align-items:center;gap:6px;color:var(--text-muted);font-size:.88rem;cursor:pointer;transition:color .2s;padding:6px 0;margin-bottom:16px}
        .back-btn:hover{color:var(--accent)}

        .play-all-btn{display:inline-flex;align-items:center;gap:8px;background:var(--accent);color:#fff;border:none;border-radius:10px;padding:10px 22px;font-weight:600;cursor:pointer;transition:all .25s;font-size:.88rem}
        .play-all-btn:hover{filter:brightness(1.15);transform:translateY(-1px);box-shadow:0 4px 15px rgba(var(--accent-rgb),0.35)}

        @media(max-width:768px){
            .song-row{grid-template-columns:40px 1fr 60px}
            .song-row .song-album,.song-row .cover-img{display:none}
            .detail-panel{width:100vw}
            .player-info{flex:0 0 110px}
            .volume-wrap{display:none}
            .detail-header{flex-direction:column;align-items:center;text-align:center}
            .detail-header img{width:160px;height:160px}
            .card-grid{grid-template-columns:repeat(auto-fill,minmax(140px,1fr))}
        }
        @media(prefers-reduced-motion:reduce){*,*::before,*::after{animation-duration:.01ms!important;transition-duration:.01ms!important}}
    </style>
</head>
<body>
    <div class="bg-blob blob-1" aria-hidden="true"></div>
    <div class="bg-blob blob-2" aria-hidden="true"></div>
    <div class="bg-blob blob-3" aria-hidden="true"></div>

    <!-- 头部 -->
    <header class="site-header">
        <div class="max-w-7xl mx-auto px-4 h-16 flex items-center gap-4">
            <div class="flex items-center gap-2 flex-shrink-0">
                <i class="fa-solid fa-wave-square text-[var(--accent)] text-lg" aria-hidden="true"></i>
                <span class="logo-text">SE云音解析</span>
            </div>
            <div class="search-box flex items-center gap-2 px-4 py-2.5 flex-1 max-w-xl mx-auto">
                <i class="fa-solid fa-magnifying-glass text-[var(--text-dim)] text-sm" aria-hidden="true"></i>
                <input type="text" id="searchInput" placeholder="搜索歌曲、专辑、歌单..." aria-label="搜索" autocomplete="off">
                <kbd class="hidden sm:inline text-xs text-[var(--text-dim)] bg-[rgba(255,255,255,0.06)] px-1.5 py-0.5 rounded" aria-hidden="true">Enter</kbd>
            </div>
            <button class="ctrl-btn text-lg" id="settingsBtn" aria-label="主题设置" title="主题设置">
                <i class="fa-solid fa-palette"></i>
            </button>
        </div>
    </header>

    <!-- 标签页 -->
    <div class="max-w-7xl mx-auto px-4 pt-4 relative z-10">
        <div class="tab-bar inline-flex" id="tabBar">
            <div class="tab-item active" data-tab="song" data-type="1"><i class="fa-solid fa-music mr-1.5 text-xs"></i>单曲</div>
            <div class="tab-item" data-tab="album" data-type="10"><i class="fa-solid fa-compact-disc mr-1.5 text-xs"></i>专辑</div>
            <div class="tab-item" data-tab="playlist" data-type="1000"><i class="fa-solid fa-list mr-1.5 text-xs"></i>歌单</div>
            <div class="tab-item" data-tab="toplist" data-type="0"><i class="fa-solid fa-trophy mr-1.5 text-xs"></i>榜单</div>
        </div>
    </div>

    <!-- 主内容 -->
    <main class="relative z-10 max-w-7xl mx-auto px-4 pt-4 pb-28" style="min-height:calc(100vh - 64px - 76px)">
        <!-- 空状态 -->
        <div id="emptyState" class="empty-state flex flex-col items-center justify-center py-28">
            <i class="fa-solid fa-headphones mb-6" aria-hidden="true"></i>
            <h2 class="text-xl font-bold text-[var(--text-muted)] mb-2">搜索你喜欢的音乐</h2>
            <p class="text-[var(--text-dim)] text-sm">输入歌曲名、歌手或专辑名开始搜索，或浏览热门榜单</p>
        </div>
        <!-- 加载 -->
        <div id="loadingState" class="hidden flex flex-col items-center justify-center py-28">
            <div class="spinner mb-4"></div>
            <p class="text-[var(--text-muted)] text-sm" id="loadingText">正在加载...</p>
        </div>
        <!-- 动态内容 -->
        <div id="contentArea" class="hidden"></div>
    </main>

    <!-- 歌曲详情遮罩与面板 -->
    <div class="detail-overlay" id="detailOverlay" aria-hidden="true"></div>
    <aside class="detail-panel" id="detailPanel" role="dialog" aria-label="歌曲详情">
        <div class="p-6">
            <button class="ctrl-btn absolute top-4 right-4 text-xl" id="closeDetail" aria-label="关闭"><i class="fa-solid fa-xmark"></i></button>
            <div class="flex flex-col items-center mb-6">
                <img id="detailCover" class="detail-cover mb-5" src="" alt="封面">
                <h2 id="detailTitle" class="text-xl font-bold text-center mb-1"></h2>
                <p id="detailArtist" class="text-[var(--text-muted)] text-sm text-center mb-1"></p>
                <p id="detailAlbum" class="text-[var(--text-dim)] text-xs text-center"></p>
            </div>
            <div class="grid grid-cols-2 gap-3 mb-6 text-sm">
                <div class="bg-[rgba(255,255,255,0.03)] rounded-lg p-3">
                    <div class="text-[var(--text-dim)] text-xs mb-1">歌曲ID</div>
                    <div id="detailId" class="font-mono text-[var(--text-muted)]"></div>
                </div>
                <div class="bg-[rgba(255,255,255,0.03)] rounded-lg p-3">
                    <div class="text-[var(--text-dim)] text-xs mb-1">时长</div>
                    <div id="detailDuration" class="text-[var(--text-muted)]"></div>
                </div>
            </div>
            <div class="mb-5">
                <div class="text-sm font-semibold mb-3">选择音质</div>
                <div class="flex flex-wrap gap-2" id="qualityTags"></div>
            </div>
            <div class="flex gap-3 mb-6">
                <button class="btn-accent flex-1 flex items-center justify-center gap-2" id="playBtn"><i class="fa-solid fa-play text-sm"></i><span>在线播放</span></button>
                <button class="btn-ghost flex-1 flex items-center justify-center gap-2" id="stopDetailBtn"><i class="fa-solid fa-stop text-sm"></i><span>停止</span></button>
                <button class="btn-ghost flex-1 flex items-center justify-center gap-2" id="downloadBtn"><i class="fa-solid fa-download text-sm"></i><span>下载歌曲</span></button>
            </div>
            <div class="mb-6">
                <button class="btn-ghost w-full flex items-center justify-center gap-2 text-xs" id="copyLinkBtn"><i class="fa-solid fa-link text-xs"></i><span>获取并复制直链</span></button>
                <div id="directLink" class="hidden mt-2 bg-[rgba(255,255,255,0.03)] rounded-lg p-3 text-xs text-[var(--text-muted)] break-all font-mono"></div>
            </div>
            <div>
                <div class="text-sm font-semibold mb-3">歌词</div>
                <div id="lyricContainer" class="max-h-72 overflow-y-auto pr-2"><p class="text-[var(--text-dim)] text-sm">点击歌曲后加载歌词...</p></div>
            </div>
        </div>
    </aside>

    <!-- 播放器 -->
    <div class="player-bar" id="playerBar" role="region" aria-label="播放器">
        <img id="playerCover" class="player-cover" src="" alt="">
        <div class="player-info"><div class="title" id="playerTitle">未播放</div><div class="artist" id="playerArtist">--</div></div>
        <div class="player-controls">
            <button class="ctrl-btn" id="prevBtn" aria-label="上一首"><i class="fa-solid fa-backward-step"></i></button>
            <button class="ctrl-btn play-btn" id="playPauseBtn" aria-label="播放"><i class="fa-solid fa-play"></i></button>
            <button class="ctrl-btn" id="stopBtn" aria-label="停止" title="停止播放"><i class="fa-solid fa-stop"></i></button>
            <button class="ctrl-btn" id="nextBtn" aria-label="下一首"><i class="fa-solid fa-forward-step"></i></button>
        </div>
        <div class="progress-wrap">
            <span class="progress-time" id="currentTime">0:00</span>
            <div class="progress-bar" id="progressBar" role="slider" aria-label="进度"><div class="progress-fill" id="progressFill" style="width:0%"></div></div>
            <span class="progress-time" id="totalTime">0:00</span>
        </div>
        <div class="volume-wrap">
            <button class="ctrl-btn" id="volumeBtn" aria-label="音量"><i class="fa-solid fa-volume-high"></i></button>
            <input type="range" class="volume-slider" id="volumeSlider" min="0" max="100" value="80" aria-label="音量">
        </div>
    </div>

    <!-- 设置 -->
    <div class="settings-overlay" id="settingsOverlay"></div>
    <div class="settings-panel" id="settingsPanel" role="dialog" aria-label="主题设置">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold">主题设置</h3>
            <button class="ctrl-btn text-lg" id="closeSettings" aria-label="关闭"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="mb-6">
            <div class="text-sm font-semibold mb-3">预设主题</div>
            <div class="flex gap-3 flex-wrap" id="presetColors">
                <div class="color-preset active" data-color="#ff6b35" style="background:#ff6b35" title="炽焰橙"></div>
                <div class="color-preset" data-color="#10b981" style="background:#10b981" title="翡翠绿"></div>
                <div class="color-preset" data-color="#f59e0b" style="background:#f59e0b" title="琥珀金"></div>
                <div class="color-preset" data-color="#f43f5e" style="background:#f43f5e" title="玫瑰红"></div>
                <div class="color-preset" data-color="#06b6d4" style="background:#06b6d4" title="青碧蓝"></div>
                <div class="color-preset" data-color="#8b5cf6" style="background:#8b5cf6" title="薰衣紫"></div>
                <div class="color-preset" data-color="#ec4899" style="background:#ec4899" title="樱粉"></div>
                <div class="color-preset" data-color="#14b8a6" style="background:#14b8a6" title="蒂芙尼"></div>
            </div>
        </div>
        <div class="mb-6">
            <div class="text-sm font-semibold mb-3">自定义颜色</div>
            <div class="flex items-center gap-3">
                <div class="color-picker-wrap"><input type="color" id="customColorPicker" value="#ff6b35" aria-label="自定义颜色"></div>
                <span class="text-[var(--text-muted)] text-sm" id="colorHexDisplay">#ff6b35</span>
                <button class="btn-ghost text-xs ml-auto" id="applyCustomColor">应用</button>
            </div>
        </div>
        <div class="mb-4">
            <div class="text-sm font-semibold mb-3">外观模式</div>
            <div class="flex gap-2">
                <button class="btn-ghost flex-1 text-center mode-btn selected" data-mode="dark">深色</button>
                <button class="btn-ghost flex-1 text-center mode-btn" data-mode="dim">暗灰</button>
                <button class="btn-ghost flex-1 text-center mode-btn" data-mode="light">浅色</button>
            </div>
        </div>
        <p class="text-[var(--text-dim)] text-xs mt-4">设置自动保存在浏览器本地</p>
    </div>

    <div class="toast-container" id="toastContainer" aria-live="polite"></div>
    <audio id="audioPlayer" preload="auto"></audio>

    <script>
    /* ============================================================
       SE云音解析 - 前端逻辑
       ============================================================ */

    // 音质配置
    const QUALITY_OPTIONS = [
        { level:'standard',  label:'标准音质',   desc:'128kbps' },
        { level:'exhigh',    label:'极高音质',   desc:'320kbps' },
        { level:'lossless',  label:'无损音质',   desc:'FLAC' },
        { level:'hires',     label:'Hi-Res音质', desc:'Hi-Res' },
        { level:'jyeffect',  label:'高清环绕声', desc:'Spatial Audio' },
        { level:'sky',       label:'沉浸环绕声', desc:'Immersive' },
        { level:'jymaster',  label:'杜比全景声', desc:'Dolby Atmos' },
        { level:'jysky',     label:'超清母带',   desc:'Master' },
    ];

    // 全局状态
    const state = {
        activeTab: 'song',
        searchType: 1,
        currentView: 'empty',
        previousView: null,
        keyword: '',
        currentPage: 1,
        searchResultCount: 0,
        currentSong: null,
        playingSongId: null,
        selectedQuality: 'exhigh',
        currentLyrics: [],
        currentRawLrc: '',
        playState: 'stopped',
        playlist: [],
        playlistIndex: -1,
    };

    const $ = s => document.querySelector(s);
    const $$ = s => document.querySelectorAll(s);

    const dom = {
        searchInput: $('#searchInput'),
        tabBar: $('#tabBar'),
        emptyState: $('#emptyState'),
        loadingState: $('#loadingState'),
        loadingText: $('#loadingText'),
        contentArea: $('#contentArea'),
        detailOverlay: $('#detailOverlay'),
        detailPanel: $('#detailPanel'),
        detailCover: $('#detailCover'),
        detailTitle: $('#detailTitle'),
        detailArtist: $('#detailArtist'),
        detailAlbum: $('#detailAlbum'),
        detailId: $('#detailId'),
        detailDuration: $('#detailDuration'),
        qualityTags: $('#qualityTags'),
        playBtn: $('#playBtn'),
        downloadBtn: $('#downloadBtn'),
        copyLinkBtn: $('#copyLinkBtn'),
        directLink: $('#directLink'),
        lyricContainer: $('#lyricContainer'),
        closeDetail: $('#closeDetail'),
        playerBar: $('#playerBar'),
        playerCover: $('#playerCover'),
        playerTitle: $('#playerTitle'),
        playerArtist: $('#playerArtist'),
        playPauseBtn: $('#playPauseBtn'),
        prevBtn: $('#prevBtn'),
        nextBtn: $('#nextBtn'),
        progressBar: $('#progressBar'),
        progressFill: $('#progressFill'),
        currentTime: $('#currentTime'),
        totalTime: $('#totalTime'),
        volumeBtn: $('#volumeBtn'),
        volumeSlider: $('#volumeSlider'),
        audio: $('#audioPlayer'),
        settingsBtn: $('#settingsBtn'),
        settingsOverlay: $('#settingsOverlay'),
        settingsPanel: $('#settingsPanel'),
        closeSettings: $('#closeSettings'),
        presetColors: $('#presetColors'),
        customColorPicker: $('#customColorPicker'),
        colorHexDisplay: $('#colorHexDisplay'),
        applyCustomColor: $('#applyCustomColor'),
        toastContainer: $('#toastContainer'),
    };

    // 渲染音质标签
    function renderQualityTags() {
        dom.qualityTags.innerHTML = QUALITY_OPTIONS.map(q =>
            `<span class="quality-tag${q.level===state.selectedQuality?' selected':''}" data-level="${q.level}">${q.label}<span class="text-[var(--text-dim)] ml-1 text-[0.7rem]">${q.desc}</span></span>`
        ).join('');
    }
    renderQualityTags();

    // ============ 工具 ============
    function formatTime(s){ if(!s||isNaN(s))return'0:00';const m=Math.floor(s/60);return m+':'+String(Math.floor(s%60)).padStart(2,'0') }
    function formatMs(ms){ return formatTime(Math.round(ms/1000)) }
    function showToast(msg,type='info',dur=3000){
        const t=document.createElement('div');t.className=`toast ${type}`;t.textContent=msg;
        dom.toastContainer.appendChild(t);
        requestAnimationFrame(()=>requestAnimationFrame(()=>t.classList.add('show')));
        setTimeout(()=>{t.classList.remove('show');setTimeout(()=>t.remove(),400)},dur);
    }
    function getArtists(ar){ if(!ar||!ar.length)return'未知歌手';return ar.map(a=>a.name).join(' / ') }
    function formatCount(n){ if(!n)return'0';if(n>=100000000)return(n/100000000).toFixed(1)+'亿';if(n>=10000)return(n/10000).toFixed(1)+'万';return String(n) }

    // ============ API ============
    const API='api.php';
    async function apiCall(action,params={}){
        const q=new URLSearchParams({action,...params}).toString();
        try{const r=await fetch(API+'?'+q);if(!r.ok)throw new Error('HTTP '+r.status);return await r.json()}
        catch(e){return{code:500,msg:'请求失败: '+e.message}}
    }

    // ============ 视图管理 ============
    function showLoading(text='正在加载...'){
        dom.emptyState.classList.add('hidden');
        dom.contentArea.classList.add('hidden');
        dom.loadingState.classList.remove('hidden');
        dom.loadingText.textContent=text;
    }
    function hideLoading(){ dom.loadingState.classList.add('hidden') }
    function showContent(html){
        dom.loadingState.classList.add('hidden');
        dom.emptyState.classList.add('hidden');
        dom.contentArea.classList.remove('hidden');
        dom.contentArea.innerHTML=html;
    }
    function showEmpty(title,sub){
        dom.loadingState.classList.add('hidden');
        dom.contentArea.classList.add('hidden');
        dom.emptyState.classList.remove('hidden');
        dom.emptyState.querySelector('h2').textContent=title||'搜索你喜欢的音乐';
        dom.emptyState.querySelector('p').textContent=sub||'输入歌曲名、歌手或专辑名开始搜索，或浏览热门榜单';
    }

    // ============ 搜索 ============
    async function doSearch(keyword,page=1){
        if(!keyword.trim())return;
        state.keyword=keyword.trim();
        state.currentPage=page;
        showLoading('正在搜索...');
        const result=await apiCall('search',{keyword:state.keyword,type:state.searchType,page});
        hideLoading();
        if(result.code!==200||!result.result){showToast('搜索失败: '+(result.msg||'未知错误'),'error');showEmpty('搜索失败','请稍后重试');return}
        if(state.searchType===1) renderSongResults(result.result,page);
        else if(state.searchType===10) renderAlbumResults(result.result,page);
        else if(state.searchType===1000) renderPlaylistResults(result.result,page);
    }

    // 渲染单曲搜索结果
    function renderSongResults(result,page){
        const songs=result.songs||[];
        const total=result.songCount||0;
        if(!songs.length){showEmpty('没有找到相关歌曲','换个关键词试试');return}
        state.playlist=songs;
        state.searchResultCount=total;
        let html=`<div class="flex items-center justify-between mb-4"><h2 class="text-lg font-bold">搜索结果 <span class="text-[var(--text-muted)] text-sm font-normal ml-2">共 ${total} 首</span></h2></div>`;
        html+=`<div class="song-row text-[var(--text-dim)] text-xs mb-1" style="cursor:default"><span class="text-center">#</span><span></span><span>标题</span><span class="hidden md:block">专辑</span><span class="text-right"><i class="fa-regular fa-clock"></i></span></div>`;
        html+=songs.map((s,i)=>songRowHTML(s,i)).join('');
        html+=paginationHTML(total,page);
        showContent(html);
        bindSongClicks();
        bindPagination();
    }

    function songRowHTML(song,idx){
        const _ar = song.ar || song.artists || [];
        const _al = song.al || song.album || {};
        const _dt = song.dt || song.duration || 0;
        const cover=_al&&_al.picUrl?_al.picUrl+'?param=100y100':'';
        return `<div class="song-row fade-in-up" style="animation-delay:${idx*25}ms" data-song-index="${idx}" tabindex="0" role="listitem">
            <span class="song-index song-index-num">${idx+1}</span>
            <span class="play-icon"><i class="fa-solid fa-play text-xs"></i></span>
            ${cover?`<img class="cover-img" src="${cover}" alt="" loading="lazy">`:'<span></span>'}
            <div><div class="song-name">${song.name||'未知'}</div><div class="song-artist">${getArtists(_ar)}</div></div>
            <div class="song-album hidden md:block">${_al?_al.name:''}</div>
            <div class="song-duration">${formatMs(_dt)}</div>
        </div>`;
    }

    function bindSongClicks(){
        dom.contentArea.querySelectorAll('.song-row[data-song-index]').forEach(row=>{
            row.addEventListener('click',()=>{const i=parseInt(row.dataset.songIndex);if(state.playlist[i])openSongDetail(state.playlist[i])});
            row.addEventListener('keydown',e=>{if(e.key==='Enter')row.click()});
        });
    }

    // 渲染专辑搜索结果
    function renderAlbumResults(result,page){
        const albums=result.albums||[];
        const total=result.albumCount||0;
        if(!albums.length){showEmpty('没有找到相关专辑','换个关键词试试');return}
        let html=`<h2 class="text-lg font-bold mb-4">专辑搜索结果 <span class="text-[var(--text-muted)] text-sm font-normal ml-2">共 ${total} 个</span></h2>`;
        html+=`<div class="card-grid">${albums.map((a,i)=>albumCardHTML(a,i)).join('')}</div>`;
        html+=paginationHTML(total,page);
        showContent(html);
        bindAlbumClicks();
        bindPagination();
    }

    function albumCardHTML(a,i){
        const cover=a.picUrl?a.picUrl+'?param=300y300':'';
        return `<div class="card-item fade-in-up" style="animation-delay:${i*30}ms" data-album-id="${a.id}" tabindex="0">
            ${cover?`<img src="${cover}" alt="" loading="lazy">`:'<div style="aspect-ratio:1;background:var(--card)"></div>'}
            <div class="card-info"><div class="card-title">${a.name||'未知专辑'}</div><div class="card-sub">${a.artist?getArtists([a.artist]):''} ${a.publishTime?'· '+new Date(a.publishTime).getFullYear():''}</div></div>
        </div>`;
    }

    function bindAlbumClicks(){
        dom.contentArea.querySelectorAll('[data-album-id]').forEach(el=>{
            el.addEventListener('click',()=>openAlbumDetail(parseInt(el.dataset.albumId)));
        });
    }

    // 渲染歌单搜索结果
    function renderPlaylistResults(result,page){
        const playlists=result.playlists||[];
        const total=result.playlistCount||0;
        if(!playlists.length){showEmpty('没有找到相关歌单','换个关键词试试');return}
        let html=`<h2 class="text-lg font-bold mb-4">歌单搜索结果 <span class="text-[var(--text-muted)] text-sm font-normal ml-2">共 ${total} 个</span></h2>`;
        html+=`<div class="card-grid">${playlists.map((p,i)=>playlistCardHTML(p,i)).join('')}</div>`;
        html+=paginationHTML(total,page);
        showContent(html);
        bindPlaylistClicks();
        bindPagination();
    }

    function playlistCardHTML(p,i){
        const cover=p.coverImgUrl?p.coverImgUrl+'?param=300y300':'';
        return `<div class="card-item fade-in-up" style="animation-delay:${i*30}ms" data-playlist-id="${p.id}" tabindex="0">
            ${cover?`<img src="${cover}" alt="" loading="lazy">`:'<div style="aspect-ratio:1;background:var(--card)"></div>'}
            <div class="card-info"><div class="card-title">${p.name||'未知歌单'}</div><div class="card-sub">${p.creator?p.creator.nickname:''} · ${p.trackCount||0}首</div></div>
        </div>`;
    }

    function bindPlaylistClicks(){
        dom.contentArea.querySelectorAll('[data-playlist-id]').forEach(el=>{
            el.addEventListener('click',()=>openPlaylistDetail(parseInt(el.dataset.playlistId)));
        });
    }

    // 分页
    function paginationHTML(total,current){
        const perPage=30;const totalPages=Math.ceil(total/perPage);
        if(totalPages<=1)return'';
        let h='<div class="flex items-center justify-center gap-2 mt-6" id="paginationWrap">';
        const btn=(text,pg,dis=false,act=false)=>`<button class="btn-ghost text-sm${act?' !bg-[rgba(var(--accent-rgb),0.15)] !border-[rgba(var(--accent-rgb),0.4)] !text-[var(--accent)]':''}" data-page="${pg}" ${dis?'disabled':''}>${text}</button>`;
        h+=btn('上一页',current-1,current<=1);
        let start=Math.max(1,current-2),end=Math.min(totalPages,start+4);
        if(end-start<4)start=Math.max(1,end-4);
        for(let i=start;i<=end;i++)h+=btn(String(i),i,false,i===current);
        h+=btn('下一页',current+1,current>=totalPages);
        h+='</div>';return h;
    }
    function bindPagination(){
        dom.contentArea.querySelectorAll('[data-page]').forEach(btn=>{
            btn.addEventListener('click',()=>{const p=parseInt(btn.dataset.page);if(p>=1)doSearch(state.keyword,p)});
        });
    }

    // ============ 专辑详情 ============
    async function openAlbumDetail(id){
        state.previousView={type:'content',html:dom.contentArea.innerHTML};
        showLoading('正在加载专辑...');
        const result=await apiCall('album',{id});
        hideLoading();
        if(result.code!==200||!result.album){showToast('加载专辑失败','error');return}
        const album=result.album;
        const songs=result.songs||[];
        state.playlist=songs;
        const cover=album.picUrl?album.picUrl+'?param=500y500':'';
        let html=`<div class="back-btn" id="backBtn"><i class="fa-solid fa-arrow-left text-xs"></i> 返回</div>`;
        html+=`<div class="detail-header"><img src="${cover}" alt="" onerror="this.style.display='none'"><div class="info"><div class="text-xs text-[var(--accent)] font-semibold mb-2">专辑</div><h2>${album.name||'未知专辑'}</h2><p>${album.artist?getArtists([album.artist]):''}</p><p>${album.publishTime?new Date(album.publishTime).toLocaleDateString('zh-CN'):''} · ${songs.length}首</p><button class="play-all-btn mt-4" id="playAllBtn"><i class="fa-solid fa-play text-sm"></i> 播放全部</button></div></div>`;
        if(songs.length){
            html+=`<div class="song-row text-[var(--text-dim)] text-xs mb-1" style="cursor:default"><span class="text-center">#</span><span></span><span>标题</span><span class="hidden md:block">专辑</span><span class="text-right"><i class="fa-regular fa-clock"></i></span></div>`;
            html+=songs.map((s,i)=>songRowHTML(s,i)).join('');
        }
        showContent(html);
        document.getElementById('backBtn')?.addEventListener('click',goBack);
        document.getElementById('playAllBtn')?.addEventListener('click',()=>{if(songs.length)playSong(songs[0])});
        bindSongClicks();
    }

    // ============ 歌单详情 ============
    async function openPlaylistDetail(id){
        state.previousView={type:'content',html:dom.contentArea.innerHTML};
        showLoading('正在加载歌单...');
        const result=await apiCall('playlist',{id});
        hideLoading();
        if(result.code!==200||!result.playlist){showToast('加载歌单失败','error');return}
        const pl=result.playlist;
        const songs=pl.tracks||[];
        state.playlist=songs;
        const cover=pl.coverImgUrl?pl.coverImgUrl+'?param=500y500':'';
        let html=`<div class="back-btn" id="backBtn"><i class="fa-solid fa-arrow-left text-xs"></i> 返回</div>`;
        html+=`<div class="detail-header"><img src="${cover}" alt="" onerror="this.style.display='none'"><div class="info"><div class="text-xs text-[var(--accent)] font-semibold mb-2">歌单</div><h2>${pl.name||'未知歌单'}</h2><p>${pl.creator?pl.creator.nickname:''}</p><p>${pl.trackCount||0}首 · 播放${formatCount(pl.playCount)}次</p>${pl.description?`<p class="text-[var(--text-dim)] text-xs mt-2 line-clamp-2">${pl.description}</p>`:''}<button class="play-all-btn mt-4" id="playAllBtn"><i class="fa-solid fa-play text-sm"></i> 播放全部</button></div></div>`;
        if(songs.length){
            html+=`<div class="song-row text-[var(--text-dim)] text-xs mb-1" style="cursor:default"><span class="text-center">#</span><span></span><span>标题</span><span class="hidden md:block">专辑</span><span class="text-right"><i class="fa-regular fa-clock"></i></span></div>`;
            html+=songs.map((s,i)=>songRowHTML(s,i)).join('');
        }
        showContent(html);
        document.getElementById('backBtn')?.addEventListener('click',goBack);
        document.getElementById('playAllBtn')?.addEventListener('click',()=>{if(songs.length)playSong(songs[0])});
        bindSongClicks();
    }

    // ============ 榜单 ============
    async function loadToplist(){
        state.previousView=null;
        showLoading('正在加载榜单...');
        const result=await apiCall('toplist');
        hideLoading();
        if(result.code!==200||!result.list){showToast('加载榜单失败','error');showEmpty('榜单加载失败','请稍后重试');return}
        const lists=result.list||[];
        let html=`<h2 class="text-lg font-bold mb-4">热门榜单</h2><div class="card-grid">`;
        lists.forEach((l,i)=>{
            const cover=l.coverImgUrl?l.coverImgUrl+'?param=300y300':'';
            html+=`<div class="card-item fade-in-up" style="animation-delay:${i*25}ms" data-playlist-id="${l.id}" tabindex="0">
                ${cover?`<img src="${cover}" alt="" loading="lazy">`:'<div style="aspect-ratio:1;background:var(--card)"></div>'}
                <div class="card-info"><div class="card-title">${l.name||''}</div><div class="card-sub">${l.updateFrequency||''}</div></div>
            </div>`;
        });
        html+='</div>';
        showContent(html);
        bindPlaylistClicks();
    }

    function goBack(){
        if(state.previousView&&state.previousView.html){
            dom.contentArea.innerHTML=state.previousView.html;
            dom.contentArea.classList.remove('hidden');
            dom.emptyState.classList.add('hidden');
            dom.loadingState.classList.add('hidden');
            // 重新绑定事件
            bindSongClicks();bindAlbumClicks();bindPlaylistClicks();bindPagination();
            const backBtn=document.getElementById('backBtn');
            if(backBtn)backBtn.addEventListener('click',goBack);
            const playAllBtn=document.getElementById('playAllBtn');
            if(playAllBtn)playAllBtn.addEventListener('click',()=>{if(state.playlist.length)playSong(state.playlist[0])});
        }else{
            // 回到搜索或榜单
            if(state.activeTab==='toplist')loadToplist();
            else if(state.keyword)doSearch(state.keyword,state.currentPage);
            else showEmpty();
        }
    }

    async function openSongDetail(song){
        state.currentSong=song;
        state.selectedQuality='exhigh';
        renderQualityTags();
        const _ar = song.ar || song.artists || [];
        const _al = song.al || song.album || {};
        const _dt = song.dt || song.duration || 0;
        const cover=_al&&_al.picUrl?_al.picUrl+'?param=500y500':'';
        dom.detailCover.src=cover;
        dom.detailTitle.textContent=song.name||'未知';
        dom.detailArtist.textContent=getArtists(_ar);
        dom.detailAlbum.textContent=_al?'专辑: '+_al.name:'';
        dom.detailId.textContent=song.id;
        dom.detailDuration.textContent=formatMs(_dt);
        dom.directLink.classList.add('hidden');
        dom.detailOverlay.classList.add('open');
        dom.detailPanel.classList.add('open');

        // 关键：根据当前播放状态同步按钮文字
        updatePlayerUI();

        // 加载歌词
        state.currentLyrics = [];
        state.currentRawLrc = '';
        dom.lyricContainer.innerHTML='<p class="text-[var(--text-dim)] text-sm">歌词加载中...</p>';
        const lr=await apiCall('lyric',{id:song.id});
        renderLyric(lr);

        dom.contentArea.querySelectorAll('.song-row[data-song-index]').forEach(r=>{
            const i=parseInt(r.dataset.songIndex);
            r.classList.toggle('active',state.playlist[i]&&state.playlist[i].id===song.id);
        });
    }
    function closeSongDetail(){dom.detailOverlay.classList.remove('open');dom.detailPanel.classList.remove('open')}

    function renderLyric(result){
        state.currentRawLrc='';
        if(!result||result.code!==200){dom.lyricContainer.innerHTML='<p class="text-[var(--text-dim)] text-sm">暂无歌词</p>';return}
        const lrc=(result.lrc&&result.lrc.lyric)?result.lrc.lyric:'';
        const trc=(result.tlyric&&result.tlyric.lyric)?result.tlyric.lyric:'';
        if(!lrc.trim()){dom.lyricContainer.innerHTML='<p class="text-[var(--text-dim)] text-sm">暂无歌词</p>';return}

        // 保存原始LRC用于下载（如果有翻译，合并双语）
        if(trc.trim()){
            state.currentRawLrc=lrc+'\n\n[翻译]\n'+trc;
        }else{
            state.currentRawLrc=lrc;
        }

        const lines=lrc.split('\n');const parsed=[];
        const re=/\[(\d{2}):(\d{2})\.(\d{2,3})\]/g;
        lines.forEach(line=>{const ms=[...line.matchAll(re)];const t=line.replace(re,'').trim();if(!t)return;ms.forEach(m=>{parsed.push({time:parseInt(m[1])*60+parseInt(m[2])+parseInt(m[3].padEnd(3,'0'))/1000,text:t})})});
        parsed.sort((a,b)=>a.time-b.time);
        if(!parsed.length){dom.lyricContainer.innerHTML='<p class="text-[var(--text-dim)] text-sm">歌词解析失败</p>';return}
        state.currentLyrics=parsed;
        dom.lyricContainer.innerHTML=parsed.map((l,i)=>`<div class="lyric-line" data-lyric-index="${i}">${l.text}</div>`).join('');
    }

    // ============ 播放 ============
    async function playSong(song, quality) {
        if (!song) return;
        quality = quality || state.selectedQuality;

        // 同一首歌且非停止状态→切换暂停/继续
        if (state.playingSongId === song.id && dom.audio.src && state.playState !== 'stopped') {
            if (state.playState === 'playing') {
                dom.audio.pause();
                state.playState = 'paused';
            } else {
                dom.audio.play().then(() => { state.playState = 'playing'; updatePlayerUI(); }).catch(() => {});
                state.playState = 'playing';
            }
            updatePlayerUI();
            return;
        }

        // 不同歌或停止状态→重新获取链接播放
        playSongNew(song, quality);
    }
    function togglePlayPause() {
        if (!dom.audio.src || state.playState === 'stopped') return;

        if (state.playState === 'playing') {
            dom.audio.pause();
            state.playState = 'paused';
        } else if (state.playState === 'paused') {
            dom.audio.play().then(() => { state.playState = 'playing'; updatePlayerUI(); }).catch(() => {});
            state.playState = 'playing';
        }
        updatePlayerUI();
    }

    function stopPlay() {
        dom.audio.pause();
        dom.audio.currentTime = 0;
        state.playState = 'stopped';
        state.playingSongId = null;
        updatePlayerUI();
    }
    function updatePlayerUI() {
        // 底部播放栏按钮
        const ppIcon = dom.playPauseBtn.querySelector('i');
        ppIcon.className = state.playState === 'playing' ? 'fa-solid fa-pause' : 'fa-solid fa-play';

        // 停止按钮
        const stopBtn = document.getElementById('stopBtn');
        if (stopBtn) stopBtn.classList.toggle('stop-active', state.playState === 'stopped');

        // 详情面板按钮——关键：必须判断是否是同一首歌
        if (state.currentSong) {
            const isCurrentPlaying = (state.playingSongId === state.currentSong.id);
            if (isCurrentPlaying && state.playState === 'playing') {
                dom.playBtn.innerHTML = '<i class="fa-solid fa-pause text-sm"></i><span>暂停</span>';
            } else if (isCurrentPlaying && state.playState === 'paused') {
                dom.playBtn.innerHTML = '<i class="fa-solid fa-play text-sm"></i><span>继续播放</span>';
            } else {
                dom.playBtn.innerHTML = '<i class="fa-solid fa-play text-sm"></i><span>在线播放</span>';
            }
        }
    }
    function playPrev() {
        if (!state.playlist.length) return;
        state.playlistIndex = Math.max(0, state.playlistIndex - 1);
        const song = state.playlist[state.playlistIndex];
        if (song) {
            state.currentSong = song;
            state.playingSongId = song.id;
            playSongNew(song);
        }
    }

    function playNext() {
        if (!state.playlist.length) return;
        state.playlistIndex = Math.min(state.playlist.length - 1, state.playlistIndex + 1);
        const song = state.playlist[state.playlistIndex];
        if (song) {
            state.currentSong = song;
            state.playingSongId = song.id;
            playSongNew(song);
        }
    }

    // 强制重新获取链接播放新歌（不判断同一首）
    async function playSongNew(song, quality) {
        if (!song) return;
        quality = quality || state.selectedQuality;

        dom.playerBar.classList.add('visible');
        const _ar = song.ar || song.artists || [];
        const _al = song.al || song.album || {};
        const cover = _al && _al.picUrl ? _al.picUrl + '?param=200y200' : '';
        dom.playerCover.src = cover;
        dom.playerTitle.textContent = song.name || '未知';
        dom.playerArtist.textContent = getArtists(_ar);

        state.currentSong = song;
        state.playingSongId = song.id;
        state.playState = 'stopped';
        updatePlayerUI();

        showToast('正在获取播放链接...', 'info');
        const r = await apiCall('url', { id: song.id, level: quality });
        if (r.code !== 200 || !r.data || !r.data[0] || !r.data[0].url) {
            showToast('无法获取播放链接', 'error');
            return;
        }

        dom.audio.src = r.data[0].url;
        dom.audio.currentTime = 0;
        try {
            await dom.audio.play();
            state.playState = 'playing';
            updatePlayerUI();
            showToast('正在播放: ' + song.name, 'success');
        } catch (e) {
            showToast('播放失败', 'error');
            state.playState = 'stopped';
            updatePlayerUI();
        }
    }

    dom.audio.addEventListener('timeupdate', () => {
        const c = dom.audio.currentTime, d = dom.audio.duration || 0;
        dom.progressFill.style.width = (d > 0 ? c / d * 100 : 0) + '%';
        dom.currentTime.textContent = formatTime(c);
        dom.totalTime.textContent = formatTime(d);

        // 只有详情面板展示的歌就是当前播放的歌时，才同步歌词高亮
        if (state.currentLyrics.length && state.currentSong && state.playingSongId === state.currentSong.id && state.playState === 'playing') {
            let ai = -1;
            for (let i = state.currentLyrics.length - 1; i >= 0; i--) { if (c >= state.currentLyrics[i].time) { ai = i; break; } }
            dom.lyricContainer.querySelectorAll('.lyric-line').forEach((el, i) => el.classList.toggle('active', i === ai));
            if (ai >= 0) { const al = dom.lyricContainer.querySelector(`[data-lyric-index="${ai}"]`); if (al) al.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
        } else if (state.currentLyrics.length) {
            // 歌词面板展示的歌没在播放，清除所有高亮
            dom.lyricContainer.querySelectorAll('.lyric-line.active').forEach(el => el.classList.remove('active'));
        }
    });
    dom.audio.addEventListener('ended', () => { state.playState = 'stopped'; updatePlayerUI(); playNext(); });
    dom.audio.addEventListener('error', () => { showToast('音频加载失败', 'error'); state.playState = 'stopped'; updatePlayerUI(); });
    dom.progressBar.addEventListener('click',e=>{const r=dom.progressBar.getBoundingClientRect();if(dom.audio.duration)dom.audio.currentTime=(e.clientX-r.left)/r.width*dom.audio.duration});

    // 下载
    async function downloadSong(){
        if(!state.currentSong)return;
        const s=state.currentSong,q=state.selectedQuality;
        const _ar = s.ar || s.artists || [];
        const name=s.name+' - '+getArtists(_ar);
        showToast('正在获取下载链接...','info');
        const r=await apiCall('url',{id:s.id,level:q});
        if(r.code!==200||!r.data||!r.data[0]||!r.data[0].url){showToast('无法获取下载链接，可能是VIP或版权限制','error');return}
        const url=r.data[0].url;
        const a=document.createElement('a');
        a.href=url;
        a.download=name;
        a.target='_blank';
        document.body.appendChild(a);
        a.click();
        a.remove();
        showToast('下载已开始','success');
    }

    // 复制直链
    async function copyDirectLink(){
        if(!state.currentSong)return;
        showToast('正在获取直链...','info');
        const r=await apiCall('url',{id:state.currentSong.id,level:state.selectedQuality});
        if(r.code!==200||!r.data||!r.data[0]||!r.data[0].url){showToast('无法获取直链','error');return}
        const url=r.data[0].url;dom.directLink.textContent=url;dom.directLink.classList.remove('hidden');
        try{await navigator.clipboard.writeText(url)}catch(e){const t=document.createElement('textarea');t.value=url;document.body.appendChild(t);t.select();document.execCommand('copy');t.remove()}
        showToast('直链已复制到剪贴板','success');
    }

    function downloadLrc(){
        if(!state.currentSong){showToast('请先选择歌曲','error');return}
        if(!state.currentRawLrc){showToast('暂无歌词可下载','error');return}
        const song=state.currentSong;
        const _ar=song.ar||song.artists||[];
        const filename=(song.name||'未知')+' - '+getArtists(_ar)+'.lrc';
        const blob=new Blob([state.currentRawLrc],{type:'text/plain;charset=utf-8'});
        const url=URL.createObjectURL(blob);
        const a=document.createElement('a');
        a.href=url;
        a.download=filename;
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
        showToast('歌词下载成功','success');
    }

    // ============ 标签页切换 ============
    dom.tabBar.addEventListener('click',e=>{
        const tab=e.target.closest('.tab-item');if(!tab)return;
        dom.tabBar.querySelectorAll('.tab-item').forEach(t=>t.classList.remove('active'));
        tab.classList.add('active');
        state.activeTab=tab.dataset.tab;
        state.searchType=parseInt(tab.dataset.type);
        if(state.activeTab==='toplist'){loadToplist()}
        else if(state.keyword){doSearch(state.keyword)}
        else{showEmpty('搜索你喜欢的音乐','输入关键词开始搜索')}
    });

    // ============ 事件绑定 ============
    dom.searchInput.addEventListener('keydown',e=>{if(e.key==='Enter'){if(state.activeTab==='toplist'){dom.tabBar.querySelector('[data-tab="song"]').click()}doSearch(dom.searchInput.value)}});
    dom.detailOverlay.addEventListener('click',closeSongDetail);
    dom.closeDetail.addEventListener('click',closeSongDetail);
    dom.qualityTags.addEventListener('click',e=>{const t=e.target.closest('.quality-tag');if(!t)return;dom.qualityTags.querySelectorAll('.quality-tag').forEach(x=>x.classList.remove('selected'));t.classList.add('selected');state.selectedQuality=t.dataset.level;dom.directLink.classList.add('hidden')});
    dom.playBtn.addEventListener('click', () => {
        if (!state.currentSong) return;
        
        // 详情面板按钮：优先暂停/继续，只有停止状态才重新播放
        if (state.playingSongId === state.currentSong.id) {
            // 同一首歌
            if (state.playState === 'playing') {
                dom.audio.pause();
                state.playState = 'paused';
            } else if (state.playState === 'paused') {
                dom.audio.play().then(() => { state.playState = 'playing'; updatePlayerUI(); }).catch(() => {});
                state.playState = 'playing';
            } else {
                // stopped，重新播放
                playSongNew(state.currentSong);
                return;
            }
            updatePlayerUI();
        } else {
            // 不同歌，重新播放
            playSongNew(state.currentSong);
        }
    });
    dom.downloadBtn.addEventListener('click',downloadSong);
    document.getElementById('stopDetailBtn')?.addEventListener('click',stopPlay);
    dom.copyLinkBtn.addEventListener('click',copyDirectLink);
    document.getElementById('downloadLrcBtn')?.addEventListener('click',downloadLrc);
    dom.playPauseBtn.addEventListener('click', togglePlayPause);
    dom.prevBtn.addEventListener('click', playPrev);
    dom.nextBtn.addEventListener('click', playNext);
    document.getElementById('stopBtn')?.addEventListener('click', stopPlay);
    dom.volumeSlider.addEventListener('input',()=>{dom.audio.volume=dom.volumeSlider.value/100;updateVolumeIcon()});
    dom.volumeBtn.addEventListener('click',()=>{dom.audio.muted=!dom.audio.muted;updateVolumeIcon()});
    function updateVolumeIcon(){const i=dom.volumeBtn.querySelector('i');i.className=dom.audio.muted||dom.audio.volume===0?'fa-solid fa-volume-xmark':dom.audio.volume<0.5?'fa-solid fa-volume-low':'fa-solid fa-volume-high'}

    // 设置面板
    dom.settingsBtn.addEventListener('click',()=>{dom.settingsOverlay.classList.add('open');dom.settingsPanel.classList.add('open')});
    dom.closeSettings.addEventListener('click',closeSettings);
    dom.settingsOverlay.addEventListener('click',closeSettings);
    function closeSettings(){dom.settingsOverlay.classList.remove('open');dom.settingsPanel.classList.remove('open')}

    // 主题
    const themes={dark:{'--bg':'#08080d','--bg-secondary':'#0f0f18','--card':'rgba(255,255,255,0.035)','--card-hover':'rgba(255,255,255,0.07)','--border':'rgba(255,255,255,0.06)','--text':'#e8e8ed','--text-muted':'#6b6b7b','--text-dim':'#3d3d4d','--player-bg':'rgba(12,12,20,0.92)'},dim:{'--bg':'#1a1a2e','--bg-secondary':'#222240','--card':'rgba(255,255,255,0.05)','--card-hover':'rgba(255,255,255,0.09)','--border':'rgba(255,255,255,0.08)','--text':'#d8d8e8','--text-muted':'#8888a0','--text-dim':'#555570','--player-bg':'rgba(26,26,46,0.95)'},light:{'--bg':'#f5f5f8','--bg-secondary':'#ffffff','--card':'rgba(0,0,0,0.03)','--card-hover':'rgba(0,0,0,0.06)','--border':'rgba(0,0,0,0.08)','--text':'#1a1a2e','--text-muted':'#6b6b7b','--text-dim':'#a0a0b0','--player-bg':'rgba(255,255,255,0.95)'}};
    function hexToRgb(h){h=h.replace('#','');if(h.length===3)h=h[0]+h[0]+h[1]+h[1]+h[2]+h[2];return[parseInt(h.substring(0,2),16),parseInt(h.substring(2,4),16),parseInt(h.substring(4,6),16)]}
    function applyTheme(accent,mode){
        const rgb=hexToRgb(accent);
        document.documentElement.style.setProperty('--accent',accent);
        document.documentElement.style.setProperty('--accent-rgb',rgb.join(','));
        dom.customColorPicker.value=accent;dom.colorHexDisplay.textContent=accent;
        dom.presetColors.querySelectorAll('.color-preset').forEach(e=>e.classList.toggle('active',e.dataset.color===accent));
        Object.entries(themes[mode]||themes.dark).forEach(([k,v])=>document.documentElement.style.setProperty(k,v));
        $$('.mode-btn').forEach(b=>{const sel=b.dataset.mode===mode;b.classList.toggle('selected',sel);b.style.background=sel?`rgba(${rgb.join(',')},0.15)`:'';b.style.borderColor=sel?`rgba(${rgb.join(',')},0.4)`:'';b.style.color=sel?accent:''});
        localStorage.setItem('se_accent',accent);localStorage.setItem('se_mode',mode);
    }
    function loadTheme(){applyTheme(localStorage.getItem('se_accent')||'#ff6b35',localStorage.getItem('se_mode')||'dark')}
    dom.presetColors.addEventListener('click',e=>{const p=e.target.closest('.color-preset');if(!p)return;applyTheme(p.dataset.color,localStorage.getItem('se_mode')||'dark')});
    dom.customColorPicker.addEventListener('input',()=>dom.colorHexDisplay.textContent=dom.customColorPicker.value);
    dom.applyCustomColor.addEventListener('click',()=>{applyTheme(dom.customColorPicker.value,localStorage.getItem('se_mode')||'dark');showToast('自定义颜色已应用','success')});
    $$('.mode-btn').forEach(b=>b.addEventListener('click',()=>applyTheme(localStorage.getItem('se_accent')||'#ff6b35',b.dataset.mode)));

    // 键盘
    document.addEventListener('keydown',e=>{
        if(e.key==='Escape'){closeSongDetail();closeSettings()}
        if (e.key === ' ' && e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') { e.preventDefault(); togglePlayPause(); }
    });

    // 初始化
    loadTheme();
    dom.audio.volume=0.8;
    setTimeout(()=>dom.searchInput.focus(),300);
    </script>
</body>
</html>