//mainPage 
$("#closeChangeTeacherPage").click(function () {

    $("#changeTeacherPage").slideToggle();
});

$("#openChangeTeacherPageButton").click(function () {
    $("#changeTeacherPage").slideToggle();
});

$("#closeCreateNewClassPage").click(function () {
    $("#createNewClassPage").slideToggle();
});

$("#createNewClass").click(function () {
    $("#createNewClassPage").slideToggle();
});
$(".deletStudent").click(function () {
    $(this).closest("tr").remove();
});
//addData 
$("#onlineTestButton").click(function () {
    $("#onlineTestButton").addClass("currentSwitch");
    $("#selfTestButton").removeClass("currentSwitch");
});

$("#selfTestButton").click(function () {
    $("#onlineTestButton").removeClass("currentSwitch");
    $("#selfTestButton").addClass("currentSwitch");
});

//classData
$("#crewNumber").click(function () {
    $(".textBox").slideToggle(300);
})

//canva
const canvas = document.getElementById('autumnCanvas');
const ctx = canvas.getContext('2d');
let W = canvas.width = window.innerWidth;
let H = canvas.height = window.innerHeight;
const leafCount = 25;
const leafImage = new Image();
leafImage.src = './teacherIMGS/leaf.png';
let mouse = { x: W / 2, y: H / 2 };
const leaves = [];

class Leaf {
    constructor() {
        this.reset();
    }
    reset() {
        this.x = Math.random() * W;
        this.y = Math.random() * -H;
        this.size = 20 + Math.random() * 30;
        this.speedY = Math.random();
        this.speedX = (Math.random() - 0.5);
        this.rotation = Math.random() * 360;
        this.rotationSpeed = (Math.random() - 0.5) * 2;
    }
    update() {
        let dx = this.x - mouse.x;
        let dy = this.y - mouse.y;

        const distance = Math.sqrt(dx * dx + dy * dy);
        if (distance < 100) {
            const force = (100 - distance) / 500;
            const angle = Math.atan2(dy, dx);
            this.speedX += Math.cos(angle) * force*2 ;
            this.speedY -= Math.sin(angle) * force*2 ;
        }


        this.x += this.speedX;
        this.y += this.speedY;
        this.rotation += this.rotationSpeed;

        if (this.y > H || this.x < -50 || this.x > W + 50) {
            this.reset();
            this.y = -20;
        }
    }
    draw() {
        ctx.save();
        ctx.translate(this.x, this.y);
        ctx.rotate(this.rotation * Math.PI / 180);
        ctx.drawImage(leafImage, -this.size / 2, -this.size / 2, this.size, this.size);
        ctx.restore();
    }
}

function init() {
    for (let i = 0; i < 40; i++) {
        leaves.push(new Leaf());
    }
    animate();
}

function animate() {
    ctx.clearRect(0, 0, W, H);
    for (let leaf of leaves) {
        leaf.update();
        leaf.draw();
    }
    requestAnimationFrame(animate);
}

window.addEventListener('resize', () => {
    W = canvas.width = window.innerWidth;
    H = canvas.height = window.innerHeight;
});

canvas.addEventListener('mousemove', e => {
    mouse.x = e.clientX;
    mouse.y = e.clientY;
});

leafImage.onload = init;

