// 抽奖系统前端逻辑
class LotteryApp {
    constructor() {
        this.apiBase = '/api/lottery';
        this.init();
    }

    // 初始化应用
    init() {
        this.bindEvents();
        this.loadInitialData();
    }

    // 绑定事件
    bindEvents() {
        // 抽奖表单提交
        const lotteryForm = document.getElementById('lotteryForm');
        lotteryForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleLotterySubmit();
        });

        // 抽奖码输入实时验证
        const lotteryCodeInput = document.getElementById('lotteryCode');
        lotteryCodeInput.addEventListener('input', (e) => {
            this.validateLotteryCode(e.target.value);
        });

        // 邮箱输入验证
        const emailInput = document.getElementById('userEmail');
        emailInput.addEventListener('input', (e) => {
            this.validateEmail(e.target.value);
        });
    }

    // 加载初始数据
    async loadInitialData() {
        try {
            await Promise.all([
                this.loadPrizes(),
                this.loadLatestWinners(),
                this.loadStatistics(),
                this.loadRules()
            ]);
        } catch (error) {
            console.error('加载初始数据失败:', error);
            this.showMessage('加载数据失败，请刷新页面重试', 'error');
        }
    }

    // 加载奖品列表
    async loadPrizes() {
        try {
            const response = await this.apiRequest('/prizes');
            const prizes = response.data || [];
            this.renderPrizes(prizes);
        } catch (error) {
            console.error('加载奖品失败:', error);
        }
    }

    // 渲染奖品列表
    renderPrizes(prizes) {
        const prizesGrid = document.getElementById('prizesGrid');
        
        if (prizes.length === 0) {
            prizesGrid.innerHTML = '<p style="text-align: center; color: white;">暂无奖品</p>';
            return;
        }

        prizesGrid.innerHTML = prizes.map(prize => `
            <div class="prize-item">
                <img src="${prize.img || '/static/frontend/images/default-prize.svg'}" 
                     alt="${prize.name}" 
                     class="prize-image"
                     onerror="this.src='/static/frontend/images/default-prize.svg'">
                <div class="prize-name">${prize.name}</div>
                <div class="prize-type">${prize.type_text}</div>
            </div>
        `).join('');
    }

    // 加载最新中奖记录
    async loadLatestWinners() {
        try {
            const response = await this.apiRequest('/latest?limit=10');
            const winners = response.data || [];
            this.renderWinners(winners);
        } catch (error) {
            console.error('加载中奖记录失败:', error);
        }
    }

    // 渲染中奖记录
    renderWinners(winners) {
        const winnersList = document.getElementById('winnersList');
        
        if (winners.length === 0) {
            winnersList.innerHTML = '<p style="text-align: center; color: #999;">暂无中奖记录</p>';
            return;
        }

        winnersList.innerHTML = winners.map(winner => `
            <div class="winner-item">
                <div class="winner-avatar">
                    ${winner.user_email_masked ? winner.user_email_masked.charAt(0).toUpperCase() : '?'}
                </div>
                <div class="winner-info">
                    <div class="winner-email">${winner.user_email_masked || '匿名用户'}</div>
                    <div class="winner-prize">获得：${winner.prize_name}</div>
                    <div class="winner-time">${this.formatTime(winner.draw_time_format)}</div>
                </div>
            </div>
        `).join('');
    }

    // 加载统计信息
    async loadStatistics() {
        try {
            const response = await this.apiRequest('/statistics');
            const stats = response.data || {};
            this.renderStatistics(stats);
        } catch (error) {
            console.error('加载统计信息失败:', error);
        }
    }

    // 渲染统计信息
    renderStatistics(stats) {
        const statsGrid = document.getElementById('statsGrid');
        
        const statsData = [
            {
                icon: 'fas fa-users',
                number: stats.total_records || 0,
                label: '总参与人数'
            },
            {
                icon: 'fas fa-gift',
                number: stats.win_records || 0,
                label: '中奖人数'
            },
            {
                icon: 'fas fa-percentage',
                number: `${stats.win_rate || 0}%`,
                label: '中奖率'
            },
            {
                icon: 'fas fa-ticket-alt',
                number: stats.unused_codes || 0,
                label: '剩余抽奖码'
            }
        ];

        statsGrid.innerHTML = statsData.map(stat => `
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="${stat.icon}"></i>
                </div>
                <div class="stat-number">${stat.number}</div>
                <div class="stat-label">${stat.label}</div>
            </div>
        `).join('');
    }

    // 加载抽奖规则
    async loadRules() {
        try {
            const response = await this.apiRequest('/rules');
            const rules = response.data || {};
            this.renderRules(rules);
        } catch (error) {
            console.error('加载规则失败:', error);
        }
    }

    // 渲染抽奖规则
    renderRules(rules) {
        const rulesContent = document.getElementById('rulesContent');
        
        rulesContent.innerHTML = `
            <h3>${rules.title || '抽奖活动规则'}</h3>
            <ul>
                ${(rules.rules || []).map(rule => `<li>${rule}</li>`).join('')}
            </ul>
            ${rules.contact ? `
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
                    <p><strong>联系方式：</strong></p>
                    <p>邮箱：${rules.contact.email || ''}</p>
                    <p>电话：${rules.contact.phone || ''}</p>
                </div>
            ` : ''}
        `;
    }

    // 验证抽奖码
    validateLotteryCode(code) {
        const feedback = document.getElementById('codeFeedback');
        
        if (!code) {
            feedback.textContent = '';
            feedback.className = 'input-feedback';
            return;
        }

        // 基本格式验证
        if (code.length < 6 || code.length > 32) {
            feedback.textContent = '抽奖码长度应在6-32位之间';
            feedback.className = 'input-feedback error';
            return;
        }

        if (!/^[A-Za-z0-9\-]+$/.test(code)) {
            feedback.textContent = '抽奖码只能包含字母、数字和连字符';
            feedback.className = 'input-feedback error';
            return;
        }

        feedback.textContent = '格式正确';
        feedback.className = 'input-feedback success';
    }

    // 验证邮箱
    validateEmail(email) {
        const feedback = document.getElementById('emailFeedback');
        
        if (!email) {
            feedback.textContent = '';
            feedback.className = 'input-feedback';
            return;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            feedback.textContent = '邮箱格式不正确';
            feedback.className = 'input-feedback error';
            return;
        }

        feedback.textContent = '邮箱格式正确';
        feedback.className = 'input-feedback success';
    }

    // 处理抽奖提交
    async handleLotterySubmit() {
        const form = document.getElementById('lotteryForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);

        // 验证数据
        if (!data.code || data.code.trim() === '') {
            this.showMessage('请输入抽奖码', 'error');
            return;
        }

        if (data.user_email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.user_email)) {
            this.showMessage('邮箱格式不正确', 'error');
            return;
        }

        // 显示加载状态
        this.showLoading(true);
        this.setButtonLoading(true);

        try {
            const response = await this.apiRequest('/draw', 'POST', data);
            
            if (response.code === 200) {
                this.showLotteryResult(response.data);
                // 重新加载统计信息和中奖记录
                this.loadLatestWinners();
                this.loadStatistics();
            } else {
                this.showMessage(response.message || '抽奖失败', 'error');
            }
        } catch (error) {
            console.error('抽奖失败:', error);
            this.showMessage(error.message || '抽奖失败，请稍后重试', 'error');
        } finally {
            this.showLoading(false);
            this.setButtonLoading(false);
        }
    }

    // 显示抽奖结果
    showLotteryResult(result) {
        const resultSection = document.getElementById('resultSection');
        const resultCard = document.getElementById('resultCard');
        
        const isWin = result.is_win;
        resultCard.className = `result-card ${isWin ? 'win' : 'lose'}`;
        
        let resultHTML = `
            <div class="result-icon">
                <i class="fas ${isWin ? 'fa-trophy' : 'fa-heart'}"></i>
            </div>
            <div class="result-title">
                ${isWin ? '🎉 恭喜中奖！' : '💝 谢谢参与'}
            </div>
            <div class="result-message">
                ${result.message}
            </div>
        `;

        if (isWin && result.prize) {
            resultHTML += `
                <div class="prize-info">
                    <img src="${result.prize.img || '/static/frontend/images/default-prize.svg'}" 
                         alt="${result.prize.name}"
                         onerror="this.src='/static/frontend/images/default-prize.svg'">
                    <h4>${result.prize.name}</h4>
                    <p>类型：${result.prize.type_text}</p>
                    ${result.prize.price > 0 ? `<p>价值：¥${result.prize.price}</p>` : ''}
                </div>
            `;
        }

        resultCard.innerHTML = resultHTML;
        resultSection.style.display = 'block';
        
        // 滚动到结果区域
        resultSection.scrollIntoView({ behavior: 'smooth' });
        
        // 清空表单
        document.getElementById('lotteryForm').reset();
        document.getElementById('codeFeedback').textContent = '';
        document.getElementById('emailFeedback').textContent = '';
    }

    // API请求封装
    async apiRequest(endpoint, method = 'GET', data = null) {
        const url = this.apiBase + endpoint;
        const config = {
            method,
            headers: {
                'Content-Type': 'application/json',
            }
        };

        if (data && method !== 'GET') {
            config.body = JSON.stringify(data);
        }

        const response = await fetch(url, config);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        return await response.json();
    }

    // 显示消息提示
    showMessage(message, type = 'info') {
        const toast = document.getElementById('messageToast');
        const icon = toast.querySelector('.toast-icon');
        const text = toast.querySelector('.toast-text');
        
        // 设置图标
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            info: 'fas fa-info-circle'
        };
        
        icon.className = `toast-icon ${icons[type] || icons.info}`;
        text.textContent = message;
        toast.className = `message-toast ${type} show`;
        
        // 3秒后自动隐藏
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }

    // 显示/隐藏加载状态
    showLoading(show) {
        const overlay = document.getElementById('loadingOverlay');
        if (show) {
            overlay.classList.add('show');
        } else {
            overlay.classList.remove('show');
        }
    }

    // 设置按钮加载状态
    setButtonLoading(loading) {
        const button = document.getElementById('lotteryBtn');
        const span = button.querySelector('span');
        
        if (loading) {
            button.disabled = true;
            span.textContent = '抽奖中...';
        } else {
            button.disabled = false;
            span.textContent = '立即抽奖';
        }
    }

    // 格式化时间
    formatTime(timeStr) {
        if (!timeStr) return '';
        
        try {
            const date = new Date(timeStr);
            const now = new Date();
            const diff = now - date;
            
            if (diff < 60000) { // 1分钟内
                return '刚刚';
            } else if (diff < 3600000) { // 1小时内
                return `${Math.floor(diff / 60000)}分钟前`;
            } else if (diff < 86400000) { // 1天内
                return `${Math.floor(diff / 3600000)}小时前`;
            } else {
                return date.toLocaleDateString();
            }
        } catch (error) {
            return timeStr;
        }
    }
}

// 页面加载完成后初始化应用
document.addEventListener('DOMContentLoaded', () => {
    new LotteryApp();
});