CREATE TABLE ordenes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_orden VARCHAR(50) NOT NULL,
    pais_origen VARCHAR(50),
    grupo_destino VARCHAR(50),
    destino VARCHAR(50),
    codigo_vendedor VARCHAR(50),
    tipo_codigo_vendedor VARCHAR(20),
    fecha_orden DATE,
    estado VARCHAR(50),
    observaciones TEXT
) ENGINE=InnoDB;

CREATE TABLE productos (
    sku VARCHAR(50) PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT
) ENGINE=InnoDB;

CREATE TABLE formatos_producto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_sku VARCHAR(50) NOT NULL,
    formato VARCHAR(50) NOT NULL,
    unidades_por_caja INT,
    unidades_por_palet INT,
    FOREIGN KEY (producto_sku) REFERENCES productos(sku)
) ENGINE=InnoDB;

CREATE TABLE ordenes_productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    orden_id INT,
    formato_id INT,
    cantidad INT,
    FOREIGN KEY (orden_id) REFERENCES ordenes(id),
    FOREIGN KEY (formato_id) REFERENCES formatos_producto(id)
) ENGINE=InnoDB;

CREATE TABLE palets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_palet VARCHAR(50),
    tipo_palet VARCHAR(20),
    destino VARCHAR(50),
    grupo_destino VARCHAR(50),
    estado VARCHAR(50),
    observaciones TEXT
) ENGINE=InnoDB;

CREATE TABLE palet_lineas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    palet_id INT,
    orden_id INT,
    formato_id INT,
    cantidad INT,
    FOREIGN KEY (palet_id) REFERENCES palets(id),
    FOREIGN KEY (orden_id) REFERENCES ordenes(id),
    FOREIGN KEY (formato_id) REFERENCES formatos_producto(id)
) ENGINE=InnoDB;

CREATE TABLE asns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_asn VARCHAR(50),
    codigo_vendedor VARCHAR(50),
    destino VARCHAR(50),
    grupo_destino VARCHAR(50),
    fecha_asn DATE,
    estado VARCHAR(50),
    observaciones TEXT
) ENGINE=InnoDB;

CREATE TABLE asn_ordenes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asn_id INT,
    orden_id INT,
    FOREIGN KEY (asn_id) REFERENCES asns(id),
    FOREIGN KEY (orden_id) REFERENCES ordenes(id)
) ENGINE=InnoDB;

CREATE TABLE facturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_factura VARCHAR(50),
    destino VARCHAR(50),
    grupo_destino VARCHAR(50),
    fecha_factura DATE,
    importe_total DECIMAL(10,2),
    observaciones TEXT
) ENGINE=InnoDB;

CREATE TABLE factura_ordenes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    factura_id INT,
    orden_id INT,
    FOREIGN KEY (factura_id) REFERENCES facturas(id),
    FOREIGN KEY (orden_id) REFERENCES ordenes(id)
) ENGINE=InnoDB;

CREATE TABLE envios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    orden_id INT,
    transportista VARCHAR(100),
    tracking VARCHAR(100),
    estado_envio VARCHAR(50),
    fecha_envio DATE,
    fecha_entrega DATE,
    FOREIGN KEY (orden_id) REFERENCES ordenes(id)
) ENGINE=InnoDB;