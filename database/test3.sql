INSERT INTO asns (
    numero_asn,
    codigo_vendedor,
    destino,
    grupo_destino,
    fecha_asn,
    estado,
    observaciones
) VALUES (
    'ASN-001',
    '4G7L8',
    'XMA3',
    'España',
    '2026-04-15',
    'pendiente',
    'ASN para orden 49KSJ9PC'
);

INSERT INTO asn_ordenes (asn_id, orden_id)
SELECT 
    a.id,
    o.id
FROM asns a
JOIN ordenes o
WHERE a.numero_asn = 'ASN-001'
  AND o.numero_orden = '49KSJ9PC';



  INSERT INTO facturas (
    numero_factura,
    destino,
    grupo_destino,
    fecha_factura,
    importe_total,
    observaciones
) VALUES (
    'AMZ-001',
    'XMA3',
    'España',
    '2026-04-15',
    0.00,
    'Factura asociada al ASN-001'
);

INSERT INTO factura_ordenes (factura_id, orden_id)
SELECT 
    f.id,
    o.id
FROM facturas f
JOIN ordenes o
WHERE f.numero_factura = 'AMZ-001'
  AND o.numero_orden = '49KSJ9PC';


  SELECT 
    o.numero_orden AS 'PO',
    p.sku AS 'SKU',
    p.nombre AS 'Producto',
    f.formato AS 'Formato',
    op.cantidad AS 'Cantidad',
    op.numero_palets AS 'Palets',
    op.tipo_paletizado AS 'Tipo',
    
    a.numero_asn AS 'ASN',
    a.estado AS 'Estado ASN',
    
    fac.numero_factura AS 'Factura',
    fac.fecha_factura AS 'Fecha factura',
    
    o.destino AS 'Destino'

FROM ordenes_productos op
JOIN ordenes o ON op.orden_id = o.id
JOIN formatos_producto f ON op.formato_id = f.id
JOIN productos p ON f.producto_sku = p.sku

LEFT JOIN asn_ordenes ao ON o.id = ao.orden_id
LEFT JOIN asns a ON ao.asn_id = a.id

LEFT JOIN factura_ordenes fo ON o.id = fo.orden_id
LEFT JOIN facturas fac ON fo.factura_id = fac.id

WHERE o.numero_orden = '49KSJ9PC';


ALTER TABLE palets
ADD UNIQUE (codigo_palet);

ALTER TABLE factura_ordenes
ADD UNIQUE (orden_id);

ALTER TABLE asns
DROP COLUMN codigo_vendedor,
DROP COLUMN destino,
DROP COLUMN grupo_destino;

ALTER TABLE factura_ordenes
ADD UNIQUE (orden_id);

ALTER TABLE envios
    ADD COLUMN tipo_servicio VARCHAR(50),
    ADD COLUMN url_dachser VARCHAR(500);

    CREATE TABLE envio_ordenes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    envio_id INT NOT NULL,
    orden_id INT NOT NULL,
    FOREIGN KEY (envio_id) REFERENCES envios(id),
    FOREIGN KEY (orden_id) REFERENCES ordenes(id),
    UNIQUE (orden_id)  -- una orden solo puede estar en un envío
);

ALTER TABLE envios DROP FOREIGN KEY envios_ibfk_1;
ALTER TABLE envios DROP COLUMN orden_id;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO usuarios (nombre, usuario, password) 
VALUES ('Administrador', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');


UPDATE usuarios 
SET password = '$2y$10$8K1p/a0dR7Q5Zq3mVnJxOeW5vL9uX2yN6tF4hG7iA3bC1dE0fH2sI'
WHERE usuario = 'admin';


UPDATE usuarios SET password = 'admin123' WHERE usuario = 'admin';


SELECT id, nombre, usuario, password FROM usuarios;


UPDATE usuarios SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE usuario = 'admin';


UPDATE usuarios SET password = '$2y$10$tkbBdZuZF/.bmxMDDLjb/.GcxBLegFaSelscWIXjPZdZzWk/6KQy6' WHERE usuario = 'admin';