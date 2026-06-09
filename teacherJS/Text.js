$(document).ready(function () {
    let currentClassID = null;
    // 載入教師名稱與班級
    $.get("./api/get_teacher_materials.php", function (res) {
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
    // 點擊班級後：顯示教材清單
    $(document).on("click", ".classRoomName", function () {
        $(".classRoomName").removeClass("currentClassRoom");
        $(this).addClass("currentClassRoom");
        $('.notFound').hide();
        const classID = $(this).data("classid");
        const className = $(this).text().trim();
        currentClassID = classID; // ✅ 這裡要更新目前選擇的班級 ID




        // 取得教材資訊
        $.get(`./api/get_class_materials.php?classID=${classID}`, function (res) {
            const data = JSON.parse(res);

            $("#classNameBox").text(data.className);

            const table = $(".classTableBox");
            table.find("tr:gt(0)").remove(); // 清空表格（保留標題列）

            // ✅ 先排序：完成的放下面
            const sortedMaterials = data.materials.sort((a, b) => {
                const aFull = a.completed >= a.total ? 1 : 0;
                const bFull = b.completed >= b.total ? 1 : 0;
                return aFull - bFull;
            });

            sortedMaterials.forEach(mat => {
                const percent = mat.total === 0 ? 0 : Math.round((mat.completed / mat.total) * 100);
                if (percent > 100) {
                    percent = 100;
                }
                const barColor = percent >= 100 ? '#4CAF50' : '#888'; // 綠色或灰色
                const uniqueId = `progress-${Math.random().toString(36).substring(2, 9)}`; // 避免衝突
                const bar = `
                              <div style="width:100%; background:#eee; border-radius:5px; height:24px; position:relative;">
                                  <div id="${uniqueId}" style="width:${percent}%; background:${barColor}; height:100%; border-radius:5px;"></div>
                                  <div id="${uniqueId}-text" style="position:absolute; top:0; left:0; right:0; bottom:0; display:flex; align-items:center; justify-content:center; font-weight:bold;">
                                      ${percent}%
                                  </div>
                              </div>
                            `;
                table.append(`
                <tr class="studentRow">
                    <td>${mat.name}</td>
                    <td>${bar}</td>
                    <td>
                        <img class="deletText X_img" data-name="${mat.name}" src="./teacherIMGS/X.png" alt="notFound">
                    </td>
                </tr>
            `);
                let current = 0;
                const duration = 1000;
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


    // 刪除教材
    $(document).on("click", ".deletText", function () {
        const name = $(this).data("name");

        if (!confirm(`確定要刪除教材 "${name}" 嗎？`)) return;

        $.post("./api/delete_material.php", { name: name }, function () {
            $(".classRoomName.currentClassRoom").trigger("click");
        });
    });

    $("#updateTextBook").on("click", function () {
        if (!currentClassID) {
            alert("請先選擇班級");
            return;
        }
        window.location.href = `./addTest.html?classID=${currentClassID}`;
    });

});
