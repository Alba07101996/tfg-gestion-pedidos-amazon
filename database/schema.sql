-- =========================
-- TABLA: ordenes
-- =========================
CREATE TABLE ordenes (
    id SERIAL PRIMARY KEY,
    numero_orden VARCHAR(50) NOT NULL,
    pais_origen VARCHAR(50),
    grupo_destino VARCHAR(50),
    destino VARCHAR(50),
    codigo_vendedor VARCHAR(50),
    tipo_codigo_vendedor VARCHAR(20), -- ordering / normal
    fecha_orden DATE,
    estado VARCHAR(50),
    observaciones TEXT
);

-- =========================
-- TABLA: productos
-- =========================
CREATE TABLE productos (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    sku VARCHAR(50),
    descripcion TEXT
);

-- =========================
-- TABLA: ordenes_productos
-- =========================
CREATE TABLE ordenes_productos (
    id SERIAL PRIMARY KEY,
    orden_id INT,
    producto_id INT,
    cantidad INT,

    FOREIGN KEY (orden_id) REFERENCES ordenes(id),
    FOREIGN KEY (producto_id) REFERENCES productos(id)
);

-- =========================
-- TABLA: palets
-- =========================
CREATE TABLE palets (
    id SERIAL PRIMARY KEY,
    codigo_palet VARCHAR(50),
    tipo_palet VARCHAR(20), -- ordering / normal
    destino VARCHAR(50),
    grupo_destino VARCHAR(50),
    estado VARCHAR(50),
    observaciones TEXT
);

-- =========================
-- TABLA: palet_lineas
-- =========================
CREATE TABLE palet_lineas (
    id SERIAL PRIMARY KEY,
    palet_id INT,
    orden_id INT,
    producto_id INT,
    cantidad INT,

    FOREIGN KEY (palet_id) REFERENCES palets(id),
    FOREIGN KEY (orden_id) REFERENCES ordenes(id),
    FOREIGN KEY (producto_id) REFERENCES productos(id)
);

-- =========================
-- TABLA: asns
-- =========================
CREATE TABLE asns (
    id SERIAL PRIMARY KEY,
    numero_asn VARCHAR(50),
    codigo_vendedor VARCHAR(50),
    destino VARCHAR(50),
    grupo_destino VARCHAR(50),
    fecha_asn DATE,
    estado VARCHAR(50),
    observaciones TEXT
);

-- =========================
-- TABLA: asn_ordenes
-- =========================
CREATE TABLE asn_ordenes (
    id SERIAL PRIMARY KEY,
    asn_id INT,
    orden_id INT,

    FOREIGN KEY (asn_id) REFERENCES asns(id),
    FOREIGN KEY (orden_id) REFERENCES ordenes(id)
);

-- =========================
-- TABLA: facturas
-- =========================
CREATE TABLE facturas (
    id SERIAL PRIMARY KEY,
    numero_factura VARCHAR(50),
    destino VARCHAR(50),
    grupo_destino VARCHAR(50),
    fecha_factura DATE,
    importe_total NUMERIC(10,2),
    observaciones TEXT
);

-- =========================
-- TABLA: factura_ordenes
-- =========================
CREATE TABLE factura_ordenes (
    id SERIAL PRIMARY KEY,
    factura_id INT,
    orden_id INT,

    FOREIGN KEY (factura_id) REFERENCES facturas(id),
    FOREIGN KEY (orden_id) REFERENCES ordenes(id)
);

-- =========================
-- TABLA: envios
-- =========================
CREATE TABLE envios (
    id SERIAL PRIMARY KEY,
    orden_id INT,
    transportista VARCHAR(100),
    tracking VARCHAR(100),
    estado_envio VARCHAR(50),
    fecha_envio DATE,
    fecha_entrega DATE,

    FOREIGN KEY (orden_id) REFERENCES ordenes(id)
);