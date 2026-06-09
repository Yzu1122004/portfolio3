$(document).ready(function () {
    // 1. 取得教師名稱與班級
    $.get("./api2/classData/get_teacher_class_list.php", function (res) {
        const data = JSON.parse(res);
        $("#teacherName p").text(data.teacherName);

        $(".classRoom").empty();
        data.classes.forEach(cls => {
            $(".classRoom").append(`
                <div class="classRoomName" data-classid="${cls.ClassID}">
                    ${cls.ClassName}
                </div>
            `);
        });
    });
 $.get("./api/teacherHome/teacher.php", function (data) {
        const res = JSON.parse(data);
        

        $("#teacherAvatarImg")
            .css("background-image", `url(${res.userIMG})`)
        console.log(res.userIMG);
        $("#teacherAvatarImg").css("background-image", `url('${res.userIMG}?t=${Date.now()}')`)

        
    });
    // 2. 點選班級 → 顯示教材
    $(document).on("click", ".classRoomName", function () {
        $(".classRoomName").removeClass("currentClassRoom");
        $(this).addClass("currentClassRoom");
        const className = $(this).text().trim();
        const classID = $(this).data("classid");

        $.get(`./api2/classData/get_class_material_names.php?classID=${classID}`, function (res) {
            $("#classNameBox").text(className);
            const materials = JSON.parse(res);
            $(".textBox").empty();
            materials.forEach(name => {
                $(".textBox").append(`
                    <div class="textContent" data-name="${name}" data-classid="${classID}">${name}</div>
                `);
            });
            $(".classTableBox").find("tr:gt(0)").remove(); // 清除學生資料
        });
        $('.notFound').show();
        $(".textBox").slideToggle(300);
    });

    // 3. 點選教材 → 顯示學生完成進度
    $(document).on("click", ".textContent", function () {
        $('.notFound').hide();
        $(".textContent").removeClass("currentMaterial");
        $(this).addClass("currentMaterial");
        $(".textBox").slideToggle(300);
        const name = $(this).data("name");
        const classID = $(this).data("classid");

        $.get(`./api2/classData/get_material_progress.php?classID=${classID}&name=${name}`, function (res) {
            const result = JSON.parse(res);
            const list = result.progress;
            $("#crewNumber p").text(name);
            // 更新完成人數欄位
            // 確保 canvas 清除舊圖
            if (window.progressChart) {
                window.progressChart.destroy();
            }

            const percent = Math.round((result.completedTotal / result.studentTotal) * 100);

            const ctx = document.getElementById('progressPie').getContext('2d');
            window.progressChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['完成', '未完成'],
                    datasets: [{
                        data: [result.completedTotal, result.studentTotal - result.completedTotal],
                        backgroundColor: ['#4CAF50', '#e0e0e0'],
                        borderWidth: 0
                    }]
                },
                options: {
                    cutout: '70%',
                    responsive: false,
                    plugins: {
                        tooltip: { enabled: false },
                        legend: { display: false },
                        title: {
                            display: false,
                            text: percent + '%',
                            color: '#333',
                            font: {
                                size: 20,
                                weight: 'bold'
                            },
                            position: 'center'
                        }
                    },
                    animation: {
                        animateRotate: true,
                        duration: 1000
                    }
                },
                plugins: [{
                    id: 'centerText',
                    beforeDraw(chart) {
                        const width = chart.width;
                        const height = chart.height;
                        const ctx = chart.ctx;
                        ctx.restore();
                        const fontSize = (height / 4).toFixed(2);
                        ctx.font = fontSize + "px sans-serif";
                        ctx.textBaseline = "middle";

                        const text = percent + "%";
                        const textX = Math.round((width - ctx.measureText(text).width) / 2);
                        const textY = height / 2;

                        ctx.fillStyle = "#333";
                        ctx.fillText(text, textX, textY);
                        ctx.save();
                    }
                }]
            });


            // 照原本邏輯更新學生表格
            const table = $(".classTableBox");
            table.find("tr:gt(0)").remove();

            list.sort((a, b) => {
                const aFull = a.completed >= a.total ? 1 : 0;
                const bFull = b.completed >= b.total ? 1 : 0;
                return aFull - bFull;
            });

            list.forEach(stu => {
                const percent = stu.total === 0 ? 0 : Math.round((stu.completed / stu.total) * 100);
                const barColor = percent >= 100 ? '#4CAF50' : '#888';
                const uniqueId = `progress-${Math.random().toString(36).substring(2, 9)}`; // 避免衝突

                const progressBar = `
            <div style="width:100%; background:#eee; border-radius:5px; height:24px; position:relative;">
                <div id="${uniqueId}" style="width:${percent}%; background:${barColor}; height:100%; border-radius:5px;"></div>
                <div id="${uniqueId}-text" style="position:absolute; top:0; left:0; right:0; bottom:0; display:flex; align-items:center; justify-content:center; font-weight:bold;">
                    ${percent}%
                </div>
            </div>
        `;

                table.append(`
            <tr>
                <td>${stu.studentName}</td>
                <td>${progressBar}</td>
                <td>${stu.attemptCount}</td>
            </tr>
        `);
                let current = 0;
                const duration = 3000;
                const step = Math.ceil(percent / (duration / 10));

                const interval = setInterval(() => {
                    current += step;
                    if (current >= percent) {
                        current = percent;
                        clearInterval(interval);
                    }
                    $(`#${uniqueId}`).css("width", `${current}%`);
                    $(`#${uniqueId}-text`).text(`${current}%`);
                }, 10);
            });
        });


    });
});
