-- =========================================================
-- AZIZDEV ANIMALS
-- Database export for XAMPP / MySQL / MariaDB
-- =========================================================

DROP DATABASE IF EXISTS azizdev_animals;

CREATE DATABASE azizdev_animals
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE azizdev_animals;

-- =========================================================
-- TABLE: users
-- =========================================================

CREATE TABLE users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY unique_users_email (email)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- TABLE: images
-- =========================================================

CREATE TABLE images (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_images_user (user_id),

    CONSTRAINT fk_images_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- TABLE: categories
-- =========================================================

CREATE TABLE categories (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    icon VARCHAR(100) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY unique_categories_name (name)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- TABLE: animals
-- =========================================================

CREATE TABLE animals (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id INT UNSIGNED NULL,

    name VARCHAR(150) NOT NULL,
    scientific_name VARCHAR(200) NULL,

    short_description TEXT NULL,
    description TEXT NULL,

    diet VARCHAR(255) NULL,
    habitat VARCHAR(255) NULL,
    characteristics TEXT NULL,
    lifespan VARCHAR(100) NULL,

    image_url VARCHAR(500) NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_animals_name (name),
    KEY idx_animals_scientific_name (scientific_name),
    KEY idx_animals_category (category_id),
    KEY idx_animals_habitat (habitat),
    KEY idx_animals_diet (diet),

    CONSTRAINT fk_animals_category
        FOREIGN KEY (category_id)
        REFERENCES categories(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- TABLE: favorites
-- =========================================================

CREATE TABLE favorites (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    animal_id INT UNSIGNED NOT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY unique_user_animal (user_id, animal_id),

    KEY idx_favorites_user (user_id),
    KEY idx_favorites_animal (animal_id),

    CONSTRAINT fk_favorites_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_favorites_animal
        FOREIGN KEY (animal_id)
        REFERENCES animals(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- CATEGORIES
-- =========================================================

INSERT INTO categories (name, description, icon) VALUES
(
    'Mammals',
    'Animals that generally give birth to live young and feed them with milk.',
    'paw-print'
),
(
    'Birds',
    'Animals with feathers, wings and beaks.',
    'bird'
),
(
    'Reptiles',
    'Cold-blooded vertebrate animals such as snakes, turtles and crocodiles.',
    'snake'
),
(
    'Fish',
    'Aquatic animals that live primarily in water.',
    'fish'
),
(
    'Amphibians',
    'Animals such as frogs and salamanders that can live in aquatic and terrestrial environments.',
    'bug'
),
(
    'Insects',
    'Small arthropods with six legs and usually one or two pairs of wings.',
    'bug'
);

-- =========================================================
-- ANIMALS
-- =========================================================

INSERT INTO animals (
    category_id,
    name,
    scientific_name,
    short_description,
    description,
    diet,
    habitat,
    characteristics,
    lifespan,
    image_url
) VALUES

(
    1,
    'Lion',
    'Panthera leo',
    'A large wild cat known for its social behavior and strength.',
    'The lion is a large carnivorous mammal that lives mainly in African grasslands and savannas.',
    'Carnivore',
    'Savanna and grasslands',
    'Social, powerful predator, lives in groups called prides.',
    '10 to 14 years in the wild',
    'https://images.unsplash.com/photo-1546182990-dffeafbe841d'
),

(
    1,
    'Elephant',
    'Loxodonta africana',
    'The largest land animal on Earth.',
    'African elephants are highly intelligent social mammals recognized by their large ears, trunks and tusks.',
    'Herbivore',
    'Savanna, forests and grasslands',
    'Highly intelligent, social, excellent memory and strong family bonds.',
    '60 to 70 years',
    'https://images.unsplash.com/photo-1557050543-4d5f4e07ef46'
),

(
    1,
    'Dolphin',
    'Delphinidae',
    'An intelligent marine mammal known for its communication and social behavior.',
    'Dolphins are highly intelligent aquatic mammals that live in groups and communicate using various sounds.',
    'Carnivore',
    'Oceans and coastal waters',
    'Intelligent, social, fast swimmer and strong communication abilities.',
    '20 to 60 years depending on species',
    'https://images.unsplash.com/photo-1607153333879-c174d265f1d2'
),

(
    2,
    'Pigeon',
    'Columba livia',
    'A common bird found in cities and many natural environments.',
    'The pigeon is a widespread bird species that has adapted extremely well to urban environments.',
    'Omnivore',
    'Cities, cliffs, forests and agricultural areas',
    'Excellent navigation abilities, strong flight and adaptability.',
    '3 to 6 years in the wild',
    'https://images.unsplash.com/photo-1497206365907-f5e630693df0'
),

(
    2,
    'Eagle',
    'Aquila',
    'A powerful bird of prey known for its excellent vision.',
    'Eagles are large birds of prey with strong talons, powerful wings and exceptional eyesight.',
    'Carnivore',
    'Mountains, forests and open landscapes',
    'Excellent vision, powerful talons and strong flight.',
    '20 to 30 years',
    'https://images.unsplash.com/photo-1472396961693-142e6e269027'
),

(
    2,
    'Penguin',
    'Spheniscidae',
    'A flightless aquatic bird adapted to life in the water.',
    'Penguins are marine birds that cannot fly but are excellent swimmers.',
    'Carnivore',
    'Coastal regions and polar environments',
    'Excellent swimmer, waterproof feathers and adapted to cold environments.',
    '15 to 20 years',
    'https://images.unsplash.com/photo-1551986782-d0169b3f8fa7'
),

(
    3,
    'Crocodile',
    'Crocodylidae',
    'A large aquatic reptile with a powerful jaw.',
    'Crocodiles are ancient reptiles that live in rivers, lakes and wetlands in tropical regions.',
    'Carnivore',
    'Rivers, lakes and wetlands',
    'Powerful jaws, strong swimming ability and excellent ambush predator.',
    '50 to 70 years',
    'https://images.unsplash.com/photo-1551238968-7e3d7b1e6b0e'
),

(
    3,
    'Turtle',
    'Testudines',
    'A reptile protected by a hard shell.',
    'Turtles are reptiles characterized by their protective shells and can live in marine, freshwater or terrestrial environments.',
    'Herbivore / Omnivore',
    'Oceans, rivers, lakes and land',
    'Protective shell, slow metabolism and long lifespan.',
    '20 to 100+ years depending on species',
    'https://images.unsplash.com/photo-1507525428034-b723cf961d3e'
),

(
    4,
    'Clownfish',
    'Amphiprioninae',
    'A colorful marine fish commonly associated with sea anemones.',
    'Clownfish are small reef fish that often live in close association with sea anemones.',
    'Omnivore',
    'Coral reefs and tropical oceans',
    'Bright coloration, symbiotic relationship with sea anemones.',
    '6 to 10 years',
    'https://images.unsplash.com/photo-1544551763-46a013bb70d5'
),

(
    5,
    'Frog',
    'Anura',
    'An amphibian known for its jumping ability and aquatic life stages.',
    'Frogs are amphibians that typically begin life in water before developing into adults capable of living on land.',
    'Carnivore',
    'Ponds, wetlands and forests',
    'Excellent jumper, sensitive skin and complex life cycle.',
    '4 to 15 years depending on species',
    'https://images.unsplash.com/photo-1473448912268-2022ce9509d8'
),

(
    6,
    'Butterfly',
    'Lepidoptera',
    'A flying insect known for its colorful wings.',
    'Butterflies are insects that undergo complete metamorphosis from caterpillar to adult.',
    'Herbivore',
    'Gardens, forests and grasslands',
    'Four-stage life cycle, colorful wings and pollination role.',
    'Several weeks to several months',
    'https://images.unsplash.com/photo-1473448912268-2022ce9509d8'
);

-- =========================================================
-- INDEX VERIFICATION
-- =========================================================

SELECT 'Database created successfully' AS status;

SELECT * FROM categories;

SELECT
    animals.id,
    animals.name,
    animals.scientific_name,
    categories.name AS category,
    animals.diet,
    animals.habitat,
    animals.image_url
FROM animals
LEFT JOIN categories
    ON animals.category_id = categories.id
ORDER BY animals.name;

-- =========================================================
-- VERIFY IMAGES TABLE
-- =========================================================

SELECT
    images.id,
    images.user_id,
    images.title,
    images.image_url,
    images.created_at,
    users.name AS user_name,
    users.email
FROM images
INNER JOIN users
    ON images.user_id = users.id
ORDER BY images.created_at DESC;