INSERT IGNORE INTO productos (sku, nombre, descripcion)
VALUES (
    'ECO-301',
    'ISOPROPYL ALCOHOL 99.9%',
    'Alcohol isopropílico 99.9%'
);
INSERT INTO formatos_producto (producto_sku, formato, unidades_por_caja, unidades_por_palet)
SELECT 'ECO-301', '1L', 12, 468
WHERE NOT EXISTS (
    SELECT 1
    FROM formatos_producto
    WHERE producto_sku = 'ECO-301'
      AND formato = '1L' 
);
INSERT INTO ordenes (
    numero_orden,
    pais_origen,
    grupo_destino,
    destino,
    codigo_vendedor,
    tipo_codigo_vendedor,
    fecha_orden,
    estado,
    observaciones,
    window_start,
    window_end,
    numero_palets
) VALUES (
    '49KSJ9PC',
    'España',
    'España',
    'XMA3',
    '4G7L8',
    'ordering',
    '2026-02-26',
    'pendiente',
    'Orden real de prueba',
    '2026-04-22',
    '2026-04-30',
    6
);
INSERT INTO ordenes_productos (orden_id, formato_id, cantidad)
SELECT 
    o.id,
    f.id,
    3024
FROM ordenes o
JOIN formatos_producto f
    ON f.producto_sku = 'ECO-301'
   AND f.formato = '1L'
WHERE o.numero_orden = '49KSJ9PC';
SELECT 
    o.numero_orden AS 'PO',
    p.sku AS 'SKU',
    p.nombre AS 'Producto',
    f.formato AS 'Formato',
    o.codigo_vendedor AS 'Vendor code',
    o.fecha_orden AS 'Order date',
    op.cantidad AS 'Requested quantity',
    o.numero_palets AS 'Palets',
    o.destino AS 'Destino',
    o.window_start AS 'Window Start',
    o.window_end AS 'Window End',
    o.estado AS 'Estado'
FROM ordenes_productos op
JOIN ordenes o ON op.orden_id = o.id
JOIN formatos_producto f ON op.formato_id = f.id
JOIN productos p ON f.producto_sku = p.sku
WHERE o.numero_orden = '49KSJ9PC';

SELECT * FROM ordenes;

DELETE o1 FROM ordenes o1
JOIN ordenes o2 
ON o1.numero_orden = o2.numero_orden 
AND o1.id > o2.id;

DELETE op
FROM ordenes_productos op
JOIN ordenes o ON op.orden_id = o.id
WHERE o.numero_orden = '49KSJ9PC';
DELETE FROM ordenes
WHERE numero_orden = '49KSJ9PC';

INSERT INTO ordenes (
    numero_orden,
    pais_origen,
    grupo_destino,
    destino,
    codigo_vendedor,
    tipo_codigo_vendedor,
    fecha_orden,
    estado,
    observaciones,
    window_start,
    window_end,
    numero_palets
) VALUES (
    '49KSJ9PC',
    'España',
    'España',
    'XMA3',
    '4G7L8',
    'ordering',
    '2026-02-26',
    'pendiente',
    'Orden real de prueba',
    '2026-04-22',
    '2026-04-30',
    6
);
INSERT INTO ordenes_productos (orden_id, formato_id, cantidad)
SELECT 
    o.id,
    f.id,
    3024
FROM ordenes o
JOIN formatos_producto f
    ON f.producto_sku = 'ECO-301'
   AND f.formato = '1L'
WHERE o.numero_orden = '49KSJ9PC';

ALTER TABLE ordenes
ADD UNIQUE (numero_orden);

INSERT INTO palets (codigo_palet, tipo_palet, destino, grupo_destino, estado, observaciones)
VALUES
('PAL-001', 'ordering', 'XMA3', 'España', 'pendiente', 'Palet 1 de la orden 49KSJ9PC'),
('PAL-002', 'ordering', 'XMA3', 'España', 'pendiente', 'Palet 2 de la orden 49KSJ9PC'),
('PAL-003', 'ordering', 'XMA3', 'España', 'pendiente', 'Palet 3 de la orden 49KSJ9PC'),
('PAL-004', 'ordering', 'XMA3', 'España', 'pendiente', 'Palet 4 de la orden 49KSJ9PC'),
('PAL-005', 'ordering', 'XMA3', 'España', 'pendiente', 'Palet 5 de la orden 49KSJ9PC'),
('PAL-006', 'ordering', 'XMA3', 'España', 'pendiente', 'Palet 6 de la orden 49KSJ9PC');
INSERT INTO palet_lineas (palet_id, orden_id, formato_id, cantidad)
SELECT 
    p.id,
    o.id,
    f.id,
    504
FROM palets p
JOIN ordenes o ON o.numero_orden = '49KSJ9PC'
JOIN formatos_producto f ON f.producto_sku = 'ECO-301' AND f.formato = '1L'
WHERE p.codigo_palet IN ('PAL-001', 'PAL-002', 'PAL-003', 'PAL-004', 'PAL-005', 'PAL-006');

SELECT 
    p.codigo_palet AS 'Palet',
    p.tipo_palet AS 'Tipo',
    o.numero_orden AS 'Orden',
    pr.sku AS 'SKU',
    pr.nombre AS 'Producto',
    f.formato AS 'Formato',
    pl.cantidad AS 'Cantidad',
    p.destino AS 'Destino',
    p.estado AS 'Estado'
FROM palet_lineas pl
JOIN palets p ON pl.palet_id = p.id
JOIN ordenes o ON pl.orden_id = o.id
JOIN formatos_producto f ON pl.formato_id = f.id
JOIN productos pr ON f.producto_sku = pr.sku
WHERE o.numero_orden = '49KSJ9PC';

ALTER TABLE ordenes_productos
ADD COLUMN numero_palets INT,
ADD COLUMN unidades_por_palet INT,
ADD COLUMN tipo_paletizado VARCHAR(20);

UPDATE ordenes_productos op
JOIN ordenes o ON op.orden_id = o.id
JOIN formatos_producto f ON op.formato_id = f.id
SET 
    op.numero_palets = 6,
    op.unidades_por_palet = 504,
    op.tipo_paletizado = 'ordering'
WHERE o.numero_orden = '49KSJ9PC'
  AND f.producto_sku = 'ECO-301'
  AND f.formato = '1L';

  SELECT 
    o.numero_orden AS 'PO',
    p.sku AS 'SKU',
    p.nombre AS 'Producto',
    f.formato AS 'Formato',
    op.cantidad AS 'Cantidad total',
    op.numero_palets AS 'Palets',
    op.unidades_por_palet AS 'Unidades por palet',
    op.tipo_paletizado AS 'Tipo paletizado',
    o.destino AS 'Destino'
FROM ordenes_productos op
JOIN ordenes o ON op.orden_id = o.id
JOIN formatos_producto f ON op.formato_id = f.id
JOIN productos p ON f.producto_sku = p.sku
WHERE o.numero_orden = '49KSJ9PC';

INSERT INTO facturas (
    numero_factura,
    destino,
    grupo_destino,
    fecha_factura,
    importe_total,
    observaciones
) VALUES (
    'FAC-001',
    'XMA3',
    'España',
    '2026-04-14',
    0.00,
    'Factura de prueba para órdenes destino XMA3'
);