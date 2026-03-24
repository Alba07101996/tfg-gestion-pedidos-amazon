\# Requisitos del proyecto



\## Requisitos funcionales



\- El sistema permitirá registrar órdenes (PO) procedentes de Amazon.

\- Cada orden estará identificada por un número PO único proporcionado por Amazon.

\- El sistema permitirá asociar un código de vendedor a cada orden.

\- El sistema permitirá registrar productos dentro de cada orden.

\- Cada orden podrá contener uno o varios productos.

\- El sistema permitirá gestionar palets asociados a las órdenes.

\- El sistema permitirá asignar productos a palets.

\- El sistema permitirá gestionar ASN (Advanced Shipping Notice).

\- Un ASN podrá incluir varias órdenes.

\- El sistema permitirá gestionar facturas.

\- Una factura podrá incluir varias órdenes.

\- El sistema permitirá consultar información de órdenes, productos, palets, ASN y facturas.

\- El sistema permitirá filtrar órdenes por destino, estado o código de vendedor.



\---



\## Requisitos no funcionales



\- La aplicación será accesible desde un navegador web.

\- Los datos se almacenarán en una base de datos relacional.

\- La interfaz será clara y fácil de usar.

\- El sistema tendrá una estructura organizada y mantenible.

\- Se utilizará control de versiones con Git y GitHub.



\---



\## Reglas de negocio



\- Las órdenes son proporcionadas por Amazon y se identifican mediante un número PO único.

\- Ejemplos de PO: 4FBC9SCM, 78MCP77R.



\- Cada orden tiene un código de vendedor asociado.

\- El tipo de orden (ordering o normal) se determina a partir del código de vendedor.



\- Cada orden puede contener uno o varios productos.



\### Órdenes tipo ordering



\- En las órdenes de tipo ordering, cada palet contiene un único producto.

\- No se pueden mezclar productos distintos dentro de un mismo palet.

\- No se pueden mezclar órdenes distintas dentro de un mismo palet.

\- El número de palets se calcula en función de la cantidad pedida y la configuración logística del producto.



\### Órdenes tipo normal



\- Las órdenes de tipo normal pueden compartir palet con otras órdenes.

\- Se pueden mezclar productos en un mismo palet.

\- Solo se pueden mezclar órdenes con el mismo destino.



\### ASN



\- Un ASN puede incluir varias órdenes.

\- Las órdenes incluidas en un ASN deben tener el mismo código de vendedor.

\- Las órdenes incluidas en un ASN deben tener el mismo destino.

\- Un ASN puede incluir varios palets.



\### Facturas



\- Una factura puede incluir varias órdenes.

\- Las órdenes deben tener el mismo destino.

\- Pueden tener distinto código de vendedor.

