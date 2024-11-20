-- Table des sauces disponibles
CREATE TABLE IF NOT EXISTS sauces (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(50) NOT NULL,
    prix_supplement DECIMAL(4,2) DEFAULT 0.00,
    disponible BOOLEAN DEFAULT true
);

-- Table des fromages disponibles
CREATE TABLE IF NOT EXISTS fromages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(50) NOT NULL,
    prix_supplement DECIMAL(4,2) DEFAULT 0.00,
    disponible BOOLEAN DEFAULT true
);

-- Table des légumes disponibles
CREATE TABLE IF NOT EXISTS legumes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(50) NOT NULL,
    prix_supplement DECIMAL(4,2) DEFAULT 0.00,
    disponible BOOLEAN DEFAULT true
);

-- Table pour stocker les personnalisations des commandes
CREATE TABLE IF NOT EXISTS personnalisations_panier (
    id INT PRIMARY KEY AUTO_INCREMENT,
    panier_id INT NOT NULL,
    sauce_id INT,
    fromage_id INT,
    legumes_ids TEXT,  -- Stocké comme une liste d'IDs séparés par des virgules
    prix_total_supplements DECIMAL(4,2) DEFAULT 0.00,
    FOREIGN KEY (panier_id) REFERENCES panier(id) ON DELETE CASCADE,
    FOREIGN KEY (sauce_id) REFERENCES sauces(id),
    FOREIGN KEY (fromage_id) REFERENCES fromages(id)
);

-- Insertion des données de base
INSERT INTO sauces (nom, prix_supplement) VALUES 
('Ketchup', 0.00),
('Mayonnaise', 0.00),
('Sauce Burger', 0.50),
('Sauce BBQ', 0.50),
('Sauce Poivre', 0.50);

INSERT INTO fromages (nom, prix_supplement) VALUES 
('Cheddar', 0.00),
('Emmental', 0.50),
('Raclette', 1.00),
('Chèvre', 1.00),
('Bleu', 1.00);

INSERT INTO legumes (nom, prix_supplement) VALUES 
('Salade', 0.00),
('Tomates', 0.00),
('Oignons', 0.00),
('Cornichons', 0.00),
('Oignons caramélisés', 0.50),
('Champignons', 0.50),
('Avocat', 1.00);
