-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 25, 2025 at 09:08 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `shop_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `product_image` varchar(255) NOT NULL,
  `status` enum('pending','fulfilled') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `product_name`, `product_price`, `product_image`, `status`, `created_at`) VALUES
(44, 8, 'Be Well Bee', 690.00, 'be_well_bee.jpg', 'fulfilled', '2025-03-18 17:26:53'),
(45, 8, 'Be Well Bee', 690.00, 'be_well_bee.jpg', 'fulfilled', '2025-03-18 17:35:14'),
(46, 8, 'Be Well Bee', 690.00, 'be_well_bee.jpg', 'fulfilled', '2025-03-18 18:05:42'),
(47, 8, 'Clever Lands', 469.00, 'clever_lands.jpg', 'pending', '2025-03-19 09:39:04');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` int(100) NOT NULL,
  `quantity` int(100) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `name`, `price`, `quantity`, `image`) VALUES
(56, 3, 'Clever Lands', 469, 2, 'clever_lands.jpg'),
(75, 7, 'The Everest Years', 1011, 2, 'everest.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(11, 'Action and Adventure'),
(6, 'Bildungsroman'),
(7, 'Coming-of-age story'),
(4, 'Fiction'),
(2, 'Gay-fiction'),
(22, 'Legal story'),
(21, 'Legal thriller'),
(15, 'Mystery'),
(17, 'Nature'),
(25, 'Nepali Literature'),
(16, 'Non-fiction'),
(18, 'Novel'),
(9, 'Politics'),
(23, 'Psychological Fiction'),
(20, 'Psychological thriller'),
(24, 'Psychology'),
(1, 'Romance'),
(19, 'Suspense'),
(5, 'Thriller');

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `number` varchar(12) NOT NULL,
  `message` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `message`
--

INSERT INTO `message` (`id`, `user_id`, `name`, `email`, `number`, `message`) VALUES
(2, 7, 'Ashani Dangol', 'garimahamal075@gmail.com', '9800000000', 'hiiii');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `number` varchar(12) NOT NULL,
  `email` varchar(100) NOT NULL,
  `method` varchar(50) NOT NULL,
  `address` varchar(500) NOT NULL,
  `total_products` varchar(1000) NOT NULL,
  `total_price` int(100) NOT NULL,
  `placed_on` varchar(50) NOT NULL,
  `payment_status` varchar(20) NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `name`, `number`, `email`, `method`, `address`, `total_products`, `total_price`, `placed_on`, `payment_status`) VALUES
(3, 3, 'Garima Hamal', '9840000000', 'garima123@gmail.com', 'cash on delivery', 'Street: raja birendra marg, City: Dallu, Kathmandu, State: Kathmandu', 'Boring Girls A Novel (3) , Clever Lands (1) ', 5219, '24-Jan-2025', 'completed'),
(12, 7, 'Ashani Dangol', '9876543210', 'garimahamal075@gmail.com', 'cash on delivery', 'Street: raja birendra marg, City: Dallu, Kathmandu, District: Kathmandu', 'The Bluest Eye (2) ', 1810, '13-Feb-2025', 'completed'),
(15, 8, 'Max', '9843051023', 'max984103@gmail.com', 'cash on delivery', 'Street: raja birendra marg, City: Dallu, Kathmandu, District: Kathmandu', 'Be Well Bee (1) ', 740, '18-Mar-2025', 'completed'),
(16, 8, 'Max', '9843051023', 'max984103@gmail.com', 'cash on delivery', 'Street: raja birendra marg, City: Dallu, Kathmandu, District: Kathmandu', 'Clever Lands (3) ', 1457, '19-Mar-2025', 'pending'),
(20, 7, 'Ashani Dangol', '9876543210', 'garimahamal075@gmail.com', 'cash on delivery', 'Street: raja birendra margka, City: Kathmandu, District: bagmati', 'Bash And Lucy (3) , Call Me By Your Name (4) ', 4790, '21-Mar-2025', 'pending'),
(21, 7, 'Ashani Dangol', '9876543210', 'garimahamal075@gmail.com', 'khalti', 'Street: raja birendra marg, City: Dallu, Kathmandu, District: Kathmandu', 'Blood Orange (1) ', 610, '22-Mar-2025', 'pending'),
(27, 7, 'Ashani Dangol', '9876543210', 'garimahamal075@gmail.com', 'cash on delivery', 'Street: raja birendra marg, City: Dallu, Kathmandu, District: Kathmandu', 'Balyakalka Padchapharu (2) ', 846, '23-Mar-2025', 'pending'),
(31, 8, 'Max', '9843051023', 'max984103@gmail.com', 'cash on delivery', 'Street: raja birendra marg, City: Dallu, Kathmandu, District: Kathmandu', 'Aghatit (1) ', 465, '17-Apr-2025', 'completed'),
(34, 8, 'Max', '9843051023', 'max984103@gmail.com', 'cash on delivery', 'Street: raja birendra marg, City: Dallu, Kathmandu, District: Kathmandu', 'Karodau Kastoori (1) ', 625, '24-Apr-2025', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`) VALUES
(2, 3, 1, 2),
(3, 12, 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `author` varchar(255) NOT NULL,
  `edition` varchar(255) DEFAULT NULL,
  `price` int(100) NOT NULL,
  `image` varchar(100) NOT NULL,
  `quantity` int(100) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `author`, `edition`, `price`, `image`, `quantity`, `description`) VALUES
(1, 'Call Me By Your Name', 'André Aciman', '', 750, 'call_me_by_your_name.jpg', 4, 'It\'s the summer of 1983, and precocious 17-year-old Elio Perlman is spending the days with his family at their 17th-century villa in Lombardy, Italy. He soon meets Oliver, a handsome doctoral student who\'s working as an intern for Elio\'s father. Amid the sun-drenched splendor of their surroundings, Elio and Oliver discover the heady beauty of awakening desire over the course of a summer that will alter their lives forever.'),
(2, 'Bash And Lucy', 'Lisa Cohn and Michael Cohn', '', 580, 'bash_and_lucy-2.jpg', 5, 'Talkative Bash has a big problem in soccer: His beloved dog, Lucy, is a pro at nabbing the ball, but sometimes at the wrong moment. Coach wants to ban Lucy from soccer practice and games, separating Bash from a companion who boosts his confidence in sports - and life.'),
(3, 'Boring Girls A Novel', 'Sara Taylor', '', 555, 'boring_girls_a_novel.jpg', 1, 'Rachel feels like she doesn’t fit in — until she finds heavy metal and meets Fern, a kindred spirit. The two form their own band, but the metal scene turns out to be no different than the misogynist world they want to change. Violent encounters escalate, and the friends decide there’s only one way forward . . .'),
(4, 'Clever Lands', 'Lucy Crehan', '', 469, 'clever_lands.jpg', 0, 'Secondary school teacher and education consultant Lucy Crehan was frustrated with ever-changing government policies on education; dissatisfied with a system that prioritised test scores over the promotion of creative thinking; and disheartened that the interests of children had become irrelevant.'),
(5, 'Be Well Bee', 'Cabe Lindsey', '', 690, 'be_well_bee.jpg', 10, 'I am a freakishly hairy bee, tasked with a job that only I can handle: pollinating a faraway flower. Bzzzeeom!'),
(6, 'The Bluest Eye', ' Toni Morrison', '', 880, 'eye.jpg', 5, 'Read the searing first novel from the celebrated author of Beloved, which immerses us in the tragic, torn lives of a poor black family in post-Depression 1940s Ohio. Unlovely and unloved, Pecola prays each night for blue eyes like those of her privileged white schoolfellows. At once intimate and expansive, unsparing in its truth-telling, The Bluest Eye shows how the past savagely defines the present. A powerful examination of our obsession with beauty and conformity, Toni Morrison\'s virtuosic first novel asks powerful questions about race, class, and gender with the subtlety and grace that have always characterised her writing. \'She revealed the sins of her nation, while profoundly elevating its canon. She suffused the telling of blackness with beauty, whilst steering us away from the perils of the white gaze. That\'s why she told her stories. And why we will never, ever stop reading them\' Afua Hirsch \'Discovering a writer like Toni Morrison is rarest of pleasures\' Washington Post \'When she arrived, with her first novel, The Bluest Eye, she immediately re-ordered the American literary landscape\' Ben Okri Winner of the PEN/Saul Bellow award for achievement in American fiction'),
(7, 'Blood Orange', ' Harriet Tyce', '', 600, 'bloodorage.jpg', 1, 'The story centres around Alison, a criminal barrister who drinks heavily and is having an extra-marital affair with Patrick, a colleague. Initially her husband Carl seems sensible until Alison\'s first murder case leads her to see a parallel life to her own.'),
(8, 'Bring Me Back', 'B.A. Paris', '', 420, 'bringmeback.jpg', 10, 'Finn and Layla: young and in love, their whole lives ahead of them. Driving back from a holiday in France one night, Finn pulls in to a service station, leaving Layla alone in the car. When he returns, minutes later, Layla has vanished, never to be seen again. That’s the story Finn tells the police. It’s the truth – but not the whole truth.\r\n\r\nTwelve years later, Finn has built a new life with Ellen, Layla’s sister, when he receives a phone call. Someone has seen Layla. But is it her – or someone pretending to be her? If it is her, what does she want? And what does she know about the night she disappeared?'),
(9, 'The Happy Lemon', ' Noel Lorenz', '', 1350, 'the_happy_lemon.jpg', 5, 'The Happy Lemon is a book that explores our mischievous mind. It takes control and drives on its own. Let’s read what writers from around the world has to say about it.'),
(10, 'Never Let Me Go', 'Kazuo Ishiguru', '', 958, 'go.jpg', 1, 'As a child, Kathy - now thirty-one years old - lived at Hailsham, a private school in the scenic English countryside where the children were sheltered from the outside world, brought up to believe that they were special and that their well-being was crucial not only for themselves but for the society they would eventually enter. Kathy had long ago put this idyllic past behind her, but when two of her Hailsham friends come back into her life, she stops resisting the pull of memory. And so, as her friendship with Ruth is rekindled, and as the feelings that long ago fueled her adolescent crush on Tommy begin to deepen into love, Kathy recalls their years at Hailsham. She describes happy scenes of boys and girls growing up together, unperturbed - even comforted - by their isolation. But she describes other scenes as well; of discord and misunderstanding that hint at a dark secret behind Hailsham’s nurturing facade. With the dawning clarity of hindsight, the three friends are compelled to face the truth about their childhood - and about their lives now.'),
(11, 'It Ends With Us', 'Colleen Hoover', '', 650, 'itendswithus.jpg', 15, 'Instant New York Times Bestseller Combining a captivating romance with a cast of all-too-human characters, Colleen Hoover’s It Ends With Us is an unforgettable tale of love that comes at the ultimate price.Lily hasn’t always had it easy, but that’s never stopped her from working hard for the life she wants. She’s come a long way from the small town in Maine where she grew up—she graduated from college, moved to Boston, and started her own business. So when she feels a spark with a gorgeous neurosurgeon named Ryle Kincaid, everything in Lily’s life suddenly seems almost too good to be true. Ryle is assertive, stubborn, maybe even a little arrogant. He’s also sensitive, brilliant, and has a total soft spot for Lily. And the way he looks in scrubs certainly doesn’t hurt. Lily can’t get him out of her head. But Ryle’s complete aversion to relationships is disturbing. Even as Lily finds herself becoming the exception to his “no dating” rule, she can’t help but wonder what made him that way in the first place. As questions about her new relationship overwhelm her, so do thoughts of Atlas Corrigan—her first love and a link to the past she left behind. He was her kindred spirit, her protector. When Atlas suddenly reappears, everything Lily has built with Ryle is threatened.'),
(12, 'The Everest Years', 'Chris Bonington', '', 1011, 'everest.jpg', 3, 'The first volume of Chris Bonington\'s memoirs, I Chose to Climb, was published in 1966 and told of his initiation into mountaineering, from schoolboy beginnings, culminating in the British ascent of the North Face of the Eiger and his decision to turn professional. The Next Horizon picks up where that volume left off and relates his subsequent adventures as a mountaineer, photographer, journalist and expedition leader. This final volume in the series explores the dangerous and fraught terrain of Tibet and a love affair with the world\'s highest peak. In 1985 Chris Bonington crowned an already distinguished mountaineering career by reaching the summit of Everest at the age of fifty - an achievement which won him enormous popular acclaim and affection. Here he tells of his fascination with the highest point on earth and why it meant so much to him to finally stand there himself.'),
(13, 'Why Men Love Bitches', ' Sherry Argov', '', 798, 'Bitches.jpg', 9, 'MORE THAN ONE MILLION COPIES SOLD!Do you feel like you are too nice? Sherry Argov\'s national bestseller Why Men Love Bitches delivers a unique perspective as to why men are attracted to a strong woman who stands up for herself. With saucy detail on every page, this no-nonsense guide reveals why a strong woman is much more desirable than a \"yes woman\" who routinely sacrifices herself. The New York Times bestselling author provides compelling answers to the tough questions women often ask:Why are men so romantic in the beginning and why do they change?Why do men take nice girls for granted?Why does a man respect a woman when she stands up for herself?Full of advice, hilarious real-life relationship scenarios, \"she says/he thinks\" tables, the author\'s unique \"Attraction Principles,\" and an all-new bonus chapter, Why Men Love Bitches gives you bottom-line answers. It helps you know who you are, stand your ground, and relate to men on a whole new level. Once you\'ve discovered the feisty attitude men find so magnetic, you\'ll not only increase the romantic chemistry?you\'ll gain your man\'s love and respect with far less effort.'),
(14, 'Ikigai The Japanese Secret', 'Hector Garcia Puigcerver', '', 880, 'iga.jpg', 10, 'We all have an ikigai.\r\n\r\nIt\'s the Japanese word for \'a reason to live\' or \'a reason to jump out of bed in the morning\'.\r\n\r\nIt\'s the place where your needs, desires, ambitions, and satisfaction meet. A place of balance. Small wonder that finding your ikigai is closely linked to living longer.\r\n\r\nFinding your ikigai is easier than you might think. This book will help you work out what your own ikigai really is, and equip you to change your life. You have a purpose in this world: your skills, your interests, your desires and your history have made you the perfect candidate for something. All you have to do is find it.\r\n\r\n\r\n\r\nDo that, and you can make every single day of your life joyful and meaningful.'),
(15, 'Aghatit', 'Ganesh Prasad Laath', '', 415, 'Ganesh', 11, 'A description of this book has not been provided by the author/publisher. However, Aghatit is available for purchase at Bookly.'),
(16, 'Balyakalka Padchapharu', 'Khagendra Singraula', '', 398, 'balyekal', 12, 'Childhood reminiscences of a Nepali author.'),
(19, 'Karodau Kastoori', 'Amar Neupane', '', 575, 'ka', 9, 'म त्यो दिन सम्झन्छु, जुन बेला भर्खर मेरो ओठमाथिजुँगारेखी बसेको थियो । प्वाल परेको टेनिस सुज लगाएर दिनभरि ज्याला नलिइकन सडक नाप्दै हिँड्थें । कहाँजाने, कता पुग्ने ? कुनै लक्ष्य थिएन । मान्छेहरुले मलाई बरालिएको आवारा केटो भन्थे । त्यसबखत म आफैलाई पनि लाग्थ्यो, यो संसारमा मजस्तो काम नलाग्ने मान्छे कोही छैन होला । बिनाउद्देश्य, बिनालक्ष्य हिँड्दै जाँदा एक दिन रङ्गमञ्चमा पाइला टेक्न पुगें । रङ्गमञ्चले पनि मजस्तै मान्छे खोजेको रहेछ क्यारे । रङ्गमञ्चले मलाई कहिल्यै पनि छोड्दै छोडेन । यसले मलाई सबै थोक दियो र हरिवंश बनाइदियो । यो उपन्यास मञ्च नपाएका सम्पूर्ण हरिवंशहरुको कथा हो । मलाई पात्रबिम्ब बनाएर कसैले उपन्यास लेख्ला भन्ने लागेको थिएन । यो उपन्यास ती करोडौं कस्तूरीहरुका लागि महङ्खवपूर्ण हुनेछ, जो आफ्नो जीवनको सुगन्ध थाहा नपाएर भौंतारिइहेका छन् ।— हरिवंश आचार्य, कलाकार');

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`id`, `product_id`, `category_id`) VALUES
(36, 6, 6),
(37, 6, 7),
(38, 6, 18),
(39, 7, 22),
(40, 7, 21),
(41, 7, 23),
(42, 7, 20),
(43, 7, 19),
(44, 7, 5),
(68, 13, 24),
(71, 11, 4),
(72, 11, 1),
(73, 8, 4),
(74, 8, 15),
(75, 8, 19),
(76, 8, 5),
(77, 9, 4),
(80, 2, 4),
(81, 4, 16),
(82, 3, 6),
(83, 3, 7),
(84, 3, 5),
(85, 1, 2),
(88, 14, 24),
(97, 10, 4),
(99, 16, 25),
(100, 15, 25),
(101, 19, 25),
(104, 12, 11),
(105, 5, 17);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `user_type` varchar(20) NOT NULL DEFAULT 'user',
  `phone` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `user_type`, `phone`) VALUES
(3, 'Garima Hamal', 'garima123@gmail.com', '202cb962ac59075b964b07152d234b70', 'user', '9840000000'),
(4, 'sujata', 'suta123@gmail.com', '202cb962ac59075b964b07152d234b70', 'admin', '9845123452'),
(7, 'Ashani Dangol', 'garimahamal075@gmail.com', '202cb962ac59075b964b07152d234b70', 'user', '9876543210'),
(8, 'Max', 'max984103@gmail.com', '202cb962ac59075b964b07152d234b70', 'user', '9843051023'),
(11, 'rami', 'rami@gmail.com', '202cb962ac59075b964b07152d234b70', 'user', '9700000000'),
(16, 'Cole', 'cole12@gmail.com', '202cb962ac59075b964b07152d234b70', 'user', '9811111111');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD CONSTRAINT `product_categories_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
