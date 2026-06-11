# 🎵 SE云音解析

[![GPL-3.0 License](https://img.shields.io/badge/License-GPL%203.0-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.4-8892BF.svg)](https://php.net)
[![Frontend](https://img.shields.io/badge/Frontend-TailwindCSS-06B6D4.svg)](https://tailwindcss.com)
[![API](https://img.shields.io/badge/API-eapi%20Encrypt-green.svg)]()
[![Status](https://img.shields.io/badge/Status-Stable-brightgreen.svg)]()

一个基于 PHP 的网易云音乐在线解析、播放与下载平台。采用与网易云客户端完全一致的 eapi 加密协议，支持多种音质选择、VIP 歌曲降级解析、歌词下载，以及高度自定义的精美 UI。

![SE云音解析预览图]() 

---

## ✨ 功能特性

- 🔍 **多维搜索**：支持搜索单曲、专辑、歌单，内置热门榜单直达
- 🎵 **多音质播放**：支持 8 种音质（标准 / 极高 / 无损 / Hi-Res / 高清环绕声 / 沉浸环绕声 / 杜比全景声 / 超清母带）
- ⬇️ **一键下载**：直接获取源文件链接下载音频与 LRC 歌词
- 🔐 **eapi 加密**：核心播放链接采用与网易云 PC 客户端一致的 eapi 加密算法（AES-128-ECB），请求更稳定
- 💎 **VIP 降级解析**：常规接口解析失败时，自动降级至备用接口，突破 VIP 限制
- 📜 **实时歌词**：播放同步滚动高亮，支持双语歌词下载
- 🎨 **高度自定义 UI**：8 种预设主题色 + 自定义取色器 + 3 种外观模式（深色/暗灰/浅色），设置自动保存在浏览器本地
- 📱 **响应式设计**：完美适配桌面端与移动端浏览器
- ⏯️ **完善的播放控制**：播放、暂停、停止、上一首、下一首、进度拖拽、音量调节

---

## 📦 技术栈

- **后端**：PHP (cURL, OpenSSL, BCMath/GMP)
- **前端**：Tailwind CSS, Font Awesome, Vanilla JavaScript
- **加密**：AES-128-ECB (eapi), MD5 摘要签名

---

## 🚀 部署指南

### 环境要求

- PHP >= 7.4
- PHP 扩展：`curl`, `openssl`, `bcmath` 或 `gmp`（二选一，用于加密运算）

### 快速启动

1. **克隆项目**
   ```bash
   git clone https://github.com/your-username/se-cloudplay.git
   cd se-cloudplay
   ```

2. **启动 PHP 内置服务器（测试用）**
   ```bash
   php -S localhost:8080
   ```
   然后访问 `http://localhost:8080/index.php`

3. **生产环境部署**
   将项目文件放入 Nginx/Apache 的网站根目录，确保 Web 服务器支持 PHP。

### 目录结构

```
se-cloudplay/
├── index.php        # 前端主页面 (UI + 交互逻辑)
├── api.php          # 后端 API 代理 (eapi加密 + 数据转发)
└── README.md        # 项目说明
```

---

## 🎶 音质列表

| 标签         | 等级代码    | 说明              |
| :----------- | :---------- | :---------------- |
| 标准音质     | `standard`  | 128kbps           |
| 极高音质     | `exhigh`    | 320kbps           |
| 无损音质     | `lossless`  | FLAC              |
| Hi-Res音质   | `hires`     | Hi-Res            |
| 高清环绕声   | `jyeffect`  | Spatial Audio     |
| 沉浸环绕声   | `sky`       | Immersive         |
| 杜比全景声   | `dolby`     | Dolby Atmos       |
| 超清母带     | `jymaster`  | Master            |

---

## 📡 API 文档

后端 `api.php` 提供以下接口，均返回 JSON 格式数据：

### 搜索
- **接口**：`?action=search&keyword=关键词&type=搜索类型&page=页码`
- **类型**：1=单曲, 10=专辑, 1000=歌单

### 获取播放链接
- **接口**：`?action=url&id=歌曲ID&level=音质等级`
- **说明**：优先使用 eapi 加密获取；若返回空链接，自动降级备用接口获取。

### 获取歌词
- **接口**：`?action=lyric&id=歌曲ID`

### 获取专辑详情
- **接口**：`?action=album&id=专辑ID`
- **说明**：自动补全专辑及歌曲封面直链

### 获取歌单详情
- **接口**：`?action=playlist&id=歌单ID`
- **说明**：自动分批获取歌单内所有歌曲详情

### 获取榜单列表
- **接口**：`?action=toplist`

---

## 🛠️ 核心加密说明

本项目摒弃了传统的 weapi 加密方式，采用更稳定的 **eapi 加密协议**（参考 Python pyncm 项目）：

1. 构造请求参数 JSON，确保格式与 Python `json.dumps` 完全一致（冒号后带空格）。
2. 拼接明文：`/api/xxx-36cd479b6b5-{json}-36cd479b6b5-{md5}`
3. 使用 `AES-128-ECB` 加密（密钥：`e82ckenh8dichen8`），输出 Hex 编码。
4. 请求 `interface3.music.163.com/eapi/` 接口获取数据。

---

## ⚠️ 免责声明

本项目仅供学习与技术研究使用，请勿用于商业用途。所有音乐版权均归网易云音乐及原作者所有。请支持正版音乐，尊重知识产权。使用本项目所产生的一切法律责任与开发者无关。

---

## 📄 开源协议

本项目基于 [GPL-3.0 License](LICENSE) 开源。
