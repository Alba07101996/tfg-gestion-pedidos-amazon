\# Modelo de datos



\## Tabla: codigos\_vendedor

Guarda los códigos de vendedor y su tipo.



Campos:

\- id

\- codigo

\- tipo\_codigo (ordering / normal)

\- descripcion



\---



\## Tabla: ordenes

Guarda la información principal de cada orden recibida desde Amazon.



Campos:

\- id

\- numero\_po

\- pais

\- destino

\- codigo\_vendedor\_id

\- fecha\_orden

\- estado

\- observaciones



Notas:

\- El campo numero\_po es único.

\- Ejemplos: 4FBC9SCM, 78MCP77R.



\---



\## Tabla: productos

Guarda los productos disponibles.



Campos:

\- id

\- nombre

\- sku

\- descripcion



\---



\## Tabla: productos\_logistica

Guarda la configuración logística de cada producto.



Campos:

\- id

\- producto\_id

\- unidades\_por\_caja

\- cajas\_por\_palet

\- unidades\_por\_palet

\- observaciones



\---



\## Tabla: ordenes\_productos

Relaciona las órdenes con los productos y cantidades.



Campos:

\- id

\- orden\_id

\- producto\_id

\- cantidad\_unidades



\---



\## Tabla: palets

Guarda la información de cada palet.



Campos:

\- id

\- codigo\_palet

\- orden\_id

\- destino

\- estado

\- observaciones



\---



\## Tabla: palet\_lineas

Relaciona los palets con los productos que contienen.



Campos:

\- id

\- palet\_id

\- orden\_id

\- producto\_id

\- cantidad\_unidades

\- cantidad\_cajas



\---



\## Tabla: asns

Guarda la cabecera de cada ASN.



Campos:

\- id

\- numero\_asn

\- codigo\_vendedor\_id

\- destino

\- fecha\_asn

\- estado

\- observaciones



\---



\## Tabla: asn\_ordenes

Relaciona los ASN con las órdenes.



Campos:

\- id

\- asn\_id

\- orden\_id



\---



\## Tabla: facturas

Guarda la cabecera de cada factura.



Campos:

\- id

\- numero\_factura

\- destino

\- fecha\_factura

\- importe\_total

\- observaciones



\---



\## Tabla: factura\_ordenes

Relaciona las facturas con las órdenes.



Campos:

\- id

\- factura\_id

\- orden\_id



\---



\## Tabla: envios

Guarda la información logística de los envíos.



Campos:

\- id

\- orden\_id

\- transportista

\- tracking

\- estado\_envio

\- fecha\_envio

\- fecha\_entrega

