CREATE TABLE `clients` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `first_name` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    `last_name` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    `email` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    PRIMARY KEY (`id`),
    INDEX (`last_name`) USING BTREE
) ENGINE = InnoDB;
