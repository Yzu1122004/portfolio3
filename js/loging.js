(function () {
    let isRegisterMode = false;
    const leftContent = document.getElementById('left-content');
    const middleCard = document.getElementById('middle-card');
    const toggleBtn = document.getElementById('toggle-button');

    // 先抓好外層 container，切換時把 .register-active 加/移除
    const containerEl = document.querySelector('.container');

    function switchMode() {
        if (!isRegisterMode) {
            // 由「登入」→「註冊」
            isRegisterMode = true;

            // 在左邊改成「已經註冊過了? 登入」
            leftContent.innerHTML = `
            <h2>歡迎</h2>
            <p>已經註冊過了?</p>
            <button class="register-button" id="toggle-button">登入</button>
          `;

            // 中間改成「註冊」表單
            middleCard.innerHTML = `
            <h2>註冊</h2>
            <input type="text"    id="reg-name"     placeholder="姓名" />
            <input type="text"    id="reg-account"  placeholder="帳號" />
            <input type="password" id="reg-password" placeholder="密碼" />
            <select id="reg-role">
              <option value="" disabled selected>身分(請選擇)</option>
              <option value="student">學生</option>
              <option value="teacher">教師</option>
            </select>
            <button class="login-button" id="submit-register">註冊</button>
          `;

            // **加上「.register-active」讓 CSS 動畫啟動**
            containerEl.classList.add('register-active');

        } else {
            // 由「註冊」→「登入」
            isRegisterMode = false;

            // 左邊改回「還沒註冊過? 註冊?」
            leftContent.innerHTML = `
            <h2>歡迎</h2>
            <p>還沒註冊過?</p>
            <button class="register-button" id="toggle-button">註冊?</button>
          `;

            // 中間改回「登入」表單
            middleCard.innerHTML = `
            <h2>登入</h2>
            <input type="text"    placeholder="使用者名稱/帳號" />
            <input type="password" placeholder="密碼" />
            <button class="login-button">登入</button>
          `;

            // **移除 .register-active，恢復到最初位置 / 間距**
            containerEl.classList.remove('register-active');
        }

        // 內容換過後一定要重新綁點擊事件
        bindToggleEvent();
    }

    function bindToggleEvent() {
        const newToggle = document.getElementById('toggle-button');
        if (newToggle) {
            newToggle.addEventListener('click', function (e) {
                e.preventDefault();
                switchMode();
            });
        }
    }

    // 首次綁在原本「註冊?」按鈕
    bindToggleEvent();
})();