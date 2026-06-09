-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2025-06-10 16:01:39
-- 伺服器版本： 10.4.32-MariaDB
-- PHP 版本： 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `test`
--

-- --------------------------------------------------------

--
-- 資料表結構 `class`
--

CREATE TABLE `class` (
  `ClassID` int(11) NOT NULL COMMENT '課程ID(PK)',
  `ClassName` varchar(100) NOT NULL COMMENT '課名',
  `TeacherID` int(11) NOT NULL COMMENT '老師ID(FK)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='班級資料表';

--
-- 傾印資料表的資料 `class`
--

INSERT INTO `class` (`ClassID`, `ClassName`, `TeacherID`) VALUES
(123, '3年12班', 123),
(141, '2年99', 123),
(801, '99', 999),
(803, '1年1班', 124),
(804, '2年1班', 124),
(805, '3年10班', 124),
(806, '1年2班', 125),
(807, '2年2班', 125);

-- --------------------------------------------------------

--
-- 資料表結構 `class_member`
--

CREATE TABLE `class_member` (
  `ClassID` int(11) NOT NULL COMMENT '課程ID(PK)',
  `MemberID` int(11) NOT NULL COMMENT '成員ID(FK)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='課程成員';

--
-- 傾印資料表的資料 `class_member`
--

INSERT INTO `class_member` (`ClassID`, `MemberID`) VALUES
(123, 1122002),
(123, 1122001),
(123, 789),
(801, 456),
(123, 456),
(123, 1122005);

-- --------------------------------------------------------

--
-- 資料表結構 `material`
--

CREATE TABLE `material` (
  `classID` int(11) NOT NULL,
  `materialID` varchar(11) NOT NULL COMMENT '(PK)',
  `type` enum('影片','題目') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `name` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- 傾印資料表的資料 `material`
--

INSERT INTO `material` (`classID`, `materialID`, `type`, `name`) VALUES
(123, '17486708234', '題目', '小二數學'),
(123, '17486708225', '題目', '小二數學'),
(123, '17489617542', '題目', '乘法'),
(123, '17489617521', '題目', '乘法'),
(123, '17489617507', '題目', '乘法'),
(123, '17489617514', '題目', '乘法'),
(123, '17489617545', '題目', '乘法'),
(123, '17489617513', '題目', '乘法'),
(123, '17490212599', '題目', '數學十'),
(123, '17490212529', '題目', '數學十'),
(123, '17490212605', '題目', '數學十'),
(123, '17490212534', '題目', '數學十'),
(123, '17490212574', '題目', '數學十'),
(123, '17490212585', '題目', '數學十'),
(123, '17486708683', '題目', '小三數學'),
(123, '17486708711', '題目', '小三數學'),
(123, '17486792620', '題目', '微積分'),
(123, '17486792623', '題目', '微積分'),
(123, '17486792582', '題目', '微積分'),
(123, '17486792666', '題目', '微積分'),
(123, '17486792592', '題目', '微積分'),
(123, '17486792595', '題目', '微積分'),
(123, 'v_684530aec', '影片', '測試影片5');

-- --------------------------------------------------------

--
-- 資料表結構 `materials_quations`
--

CREATE TABLE `materials_quations` (
  `MaterialID` varchar(11) NOT NULL COMMENT '(PK)',
  `name` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `teacherid` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- 傾印資料表的資料 `materials_quations`
--

INSERT INTO `materials_quations` (`MaterialID`, `name`, `teacherid`) VALUES
('17486707797', '小一數學', 0),
('17486707872', '小一數學', 0),
('17486708234', '小二數學', 0),
('17486708225', '小二數學', 0),
('17486708683', '小三數學', 0),
('17486708711', '小三數學', 0),
('17486709859', '自製小四數學題', 123),
('17486709857', '自製小四數學題', 123),
('17486709800', '自製小四數學題', 123),
('17486709812', '自製小四數學題', 123),
('17486792620', '微積分', 123),
('17486792623', '微積分', 123),
('17486792582', '微積分', 123),
('17486792666', '微積分', 123),
('17486792592', '微積分', 123),
('17486792595', '微積分', 123),
('17488670548', 'phph', 123),
('17489617542', '乘法', 123),
('17489617521', '乘法', 123),
('17489617507', '乘法', 123),
('17489617514', '乘法', 123),
('17489617545', '乘法', 123),
('17489617513', '乘法', 123),
('17490212599', '數學十', 123),
('17490212529', '數學十', 123),
('17490212605', '數學十', 123),
('17490212534', '數學十', 123),
('17490212574', '數學十', 123),
('17490212585', '數學十', 123),
('17493066864', '5', 123),
('17493066898', '5', 123),
('17493066899', '5', 123),
('17493066826', '5', 123),
('17493066860', '5', 123);

-- --------------------------------------------------------

--
-- 資料表結構 `question`
--

CREATE TABLE `question` (
  `MaterialID` varchar(11) NOT NULL COMMENT '(PK)',
  `type` enum('單選','多選','題目文字','填空') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `answer` set('a','b','c','d') CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `a_options` text CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `b_options` text CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_options` text CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `d_options` text CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `text_or_answer` text CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '放題目文字或填空答案\r\n',
  `question_order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- 傾印資料表的資料 `question`
--

INSERT INTO `question` (`MaterialID`, `type`, `answer`, `a_options`, `b_options`, `c_options`, `d_options`, `text_or_answer`, `question_order`) VALUES
('17486707797', '題目文字', NULL, NULL, NULL, NULL, NULL, '1+1=?', 1),
('17486707872', '填空', NULL, NULL, NULL, NULL, NULL, '2', 2),
('17486708234', '題目文字', NULL, NULL, NULL, NULL, NULL, '2*2=?', 1),
('17486708225', '單選', 'd', '1', '2', '3', '4', NULL, 2),
('17486708683', '題目文字', NULL, NULL, NULL, NULL, NULL, '1*2-9', 1),
('17486708711', '填空', NULL, NULL, NULL, NULL, NULL, '-7', 2),
('17486709859', '題目文字', NULL, NULL, NULL, NULL, NULL, '9/5+(7-1)=', 1),
('17486709857', '多選', 'c,d', '2', '5', '4', '4', NULL, 2),
('17486709800', '題目文字', NULL, NULL, NULL, NULL, NULL, '9*9*9+7+', 3),
('17486709812', '填空', NULL, NULL, NULL, NULL, NULL, '88', 4),
('17486792620', '題目文字', NULL, NULL, NULL, NULL, NULL, '333', 1),
('17486792623', '填空', NULL, NULL, NULL, NULL, NULL, '744', 2),
('17486792582', '填空', NULL, NULL, NULL, NULL, NULL, '74', 3),
('17486792666', '填空', NULL, NULL, NULL, NULL, NULL, '4', 4),
('17486792592', '題目文字', NULL, NULL, NULL, NULL, NULL, '44', 5),
('17486792595', '填空', NULL, NULL, NULL, NULL, NULL, '7', 6),
('17488670548', '題目文字', NULL, NULL, NULL, NULL, NULL, 'phph', 1),
('17489617542', '題目文字', NULL, NULL, NULL, NULL, NULL, '2*2=?', 1),
('17489617521', '填空', NULL, NULL, NULL, NULL, NULL, '4', 2),
('17489617507', '題目文字', NULL, NULL, NULL, NULL, NULL, '9*9+?', 3),
('17489617514', '多選', 'a,c', '81', '18', '9*9', '6', NULL, 4),
('17489617545', '題目文字', NULL, NULL, NULL, NULL, NULL, '6*3/3+?', 5),
('17489617513', '單選', 'd', '1', '2', '3', '6', NULL, 6),
('17490212599', '題目文字', NULL, NULL, NULL, NULL, NULL, '1+1=?', 1),
('17490212529', '填空', NULL, NULL, NULL, NULL, NULL, '2', 2),
('17490212605', '題目文字', NULL, NULL, NULL, NULL, NULL, '9-5=?', 3),
('17490212534', '單選', 'd', '1', '2', '3', '4', NULL, 4),
('17490212574', '題目文字', NULL, NULL, NULL, NULL, NULL, '6*6', 5),
('17490212585', '多選', 'a,b', '6*6', '36', '4', '1', NULL, 6),
('17493066864', '題目文字', NULL, NULL, NULL, NULL, NULL, '0', 1),
('17493066898', '題目文字', NULL, NULL, NULL, NULL, NULL, '0', 2),
('17493066899', '題目文字', NULL, NULL, NULL, NULL, NULL, '0', 3),
('17493066826', '填空', NULL, NULL, NULL, NULL, NULL, '0', 4),
('17493066860', '題目文字', NULL, NULL, NULL, NULL, NULL, '0', 5);

-- --------------------------------------------------------

--
-- 資料表結構 `student`
--

CREATE TABLE `student` (
  `StudentID` int(11) NOT NULL COMMENT '學生ID(PK)',
  `MaterialID` int(11) NOT NULL COMMENT '問題ID',
  `ClassID` int(11) NOT NULL COMMENT '班級ID',
  `Answer` text DEFAULT NULL COMMENT '作答內容',
  `IsCorrect` tinyint(1) NOT NULL DEFAULT 0 COMMENT '答案判斷',
  `AttemptCount` int(11) NOT NULL DEFAULT 1 COMMENT '作答次數'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='學生資料表';

--
-- 傾印資料表的資料 `student`
--

INSERT INTO `student` (`StudentID`, `MaterialID`, `ClassID`, `Answer`, `IsCorrect`, `AttemptCount`) VALUES
(456, 333, 123, '0', 1, 1);

-- --------------------------------------------------------

--
-- 資料表結構 `student_completed`
--

CREATE TABLE `student_completed` (
  `material_name` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'PK',
  `classID` int(11) NOT NULL,
  `complete_number` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- 傾印資料表的資料 `student_completed`
--

INSERT INTO `student_completed` (`material_name`, `classID`, `complete_number`) VALUES
('小二數學', 123, 1),
('影片', 123, 2),
('微積分', 123, 2);

-- --------------------------------------------------------

--
-- 資料表結構 `student_complet_quetion`
--

CREATE TABLE `student_complet_quetion` (
  `studentID` int(11) NOT NULL,
  `name` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `classID` int(11) NOT NULL,
  `complet_number` int(11) NOT NULL,
  `attemptCount` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- 傾印資料表的資料 `student_complet_quetion`
--

INSERT INTO `student_complet_quetion` (`studentID`, `name`, `classID`, `complet_number`, `attemptCount`) VALUES
(456, '微積分', 123, 3, 16),
(789, '微積分', 123, 0, 20),
(1122001, '微積分', 123, 3, 20),
(1122002, '微積分', 123, 4, 20),
(456, '小二數學', 123, 1, 14),
(0, '小二數學', 123, 0, 0),
(0, '自製小四數學題', 123, 0, 0),
(456, '自製小四數學題', 123, 0, 3),
(0, '微積分', 123, 0, 0),
(1122003, '微積分', 123, 0, 1),
(1122004, '微積分', 123, 4, 2),
(0, '乘法', 123, 0, 0),
(1122004, '乘法', 123, 2, 8);

-- --------------------------------------------------------

--
-- 資料表結構 `user`
--

CREATE TABLE `user` (
  `UserID` int(11) NOT NULL COMMENT '使用者ID(PK)',
  `Account` varchar(50) NOT NULL COMMENT '帳號',
  `Password` varchar(255) NOT NULL COMMENT '密碼',
  `Name` varchar(100) NOT NULL COMMENT '姓名',
  `Role` enum('student','teacher') NOT NULL COMMENT '身分',
  `userIMG` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='使用者資料表';

--
-- 傾印資料表的資料 `user`
--

INSERT INTO `user` (`UserID`, `Account`, `Password`, `Name`, `Role`, `userIMG`) VALUES
(123, '123', '123', '張一一', 'teacher', '../網頁資料庫(整合)/api/userImg/teacher_123.png'),
(124, '124', '124', '張一二', 'teacher', './teacherIMGS/facebook-user.jpg'),
(125, '125', '125', '張一三', 'teacher', './teacherIMGS/facebook-user.jpg'),
(126, '126', '126', '張一四', 'teacher', './teacherIMGS/facebook-user.jpg'),
(456, '456', '123', '成衣二', 'student', ''),
(789, '789', '123', '張惠妹', 'student', ''),
(999, '999', '999', '誠為', 'teacher', './teacherIMGS/facebook-user.jpg'),
(1122001, '1122001名', '123123', '姿佳安', 'student', ''),
(1122002, '1122002名', '123123', '陳娥世', 'student', ''),
(1122003, '123123', '123123', '測試1', 'student', ''),
(1122004, '987', '987987', '蔡育勝', 'student', ''),
(1122005, '369', '369369', '張家', 'student', '');

-- --------------------------------------------------------

--
-- 資料表結構 `video`
--

CREATE TABLE `video` (
  `MaterialID` varchar(11) NOT NULL,
  `content` text NOT NULL,
  `teacherID` int(11) DEFAULT NULL,
  `name` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- 傾印資料表的資料 `video`
--

INSERT INTO `video` (`MaterialID`, `content`, `teacherID`, `name`) VALUES
('v_684530aec', '../api2/makeQ/videos/v_684530aeca369.mp4', 123, '測試影片5');

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `class`
--
ALTER TABLE `class`
  ADD PRIMARY KEY (`ClassID`);

--
-- 資料表索引 `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Account` (`Account`);

--
-- 資料表索引 `video`
--
ALTER TABLE `video`
  ADD PRIMARY KEY (`MaterialID`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `class`
--
ALTER TABLE `class`
  MODIFY `ClassID` int(11) NOT NULL AUTO_INCREMENT COMMENT '課程ID(PK)', AUTO_INCREMENT=808;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `user`
--
ALTER TABLE `user`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT COMMENT '使用者ID(PK)', AUTO_INCREMENT=1122006;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
