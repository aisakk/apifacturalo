<?xml version="1.0" encoding="utf-8" standalone="no"?>
<DebitNote xmlns="urn:oasis:names:specification:ubl:schema:xsd:DebitNote-2"
           xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
           xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
           xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
           xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2">
    <ext:UBLExtensions>
        <ext:UBLExtension>
            <ext:ExtensionContent/>
        </ext:UBLExtension>
    </ext:UBLExtensions>
    <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
    <cbc:CustomizationID>2.0</cbc:CustomizationID>
    <cbc:ID>{{ doc.serie }}-{{ doc.correlativo }}</cbc:ID>
    <cbc:IssueDate>{{ doc.fechaEmision|date('Y-m-d') }}</cbc:IssueDate>
    <cbc:IssueTime>{{ doc.fechaEmision|date('H:i:s') }}</cbc:IssueTime>
    {% for leg in doc.legends -%}
        <cbc:Note languageLocaleID="{{ leg.code }}"><![CDATA[{{ leg.value }}]]></cbc:Note>
    {% endfor -%}
    <cbc:DocumentCurrencyCode>{{ doc.tipoMoneda }}</cbc:DocumentCurrencyCode>
    <cac:DiscrepancyResponse>
        <cbc:ReferenceID>{{ doc.numDocfectado }}</cbc:ReferenceID>
        <cbc:ResponseCode>{{ doc.codMotivo }}</cbc:ResponseCode>
        <cbc:Description>{{ doc.desMotivo }}</cbc:Description>
    </cac:DiscrepancyResponse>
    {% if doc.compra -%}
    <cac:OrderReference>
        <cbc:ID>{{ doc.compra }}</cbc:ID>
    </cac:OrderReference>
    {% endif -%}
    <cac:BillingReference>
        <cac:InvoiceDocumentReference>
            <cbc:ID>{{ doc.numDocfectado }}</cbc:ID>
            <cbc:DocumentTypeCode>{{ doc.tipDocAfectado }}</cbc:DocumentTypeCode>
        </cac:InvoiceDocumentReference>
    </cac:BillingReference>
    {% if doc.guias -%}
    {% for guia in doc.guias -%}
    <cac:DespatchDocumentReference>
        <cbc:ID>{{ guia.nroDoc }}</cbc:ID>
        <cbc:DocumentTypeCode>{{ guia.tipoDoc }}</cbc:DocumentTypeCode>
    </cac:DespatchDocumentReference>
    {% endfor -%}
    {% endif -%}
    {% if doc.relDocs -%}
    {% for rel in doc.relDocs -%}
    <cac:AdditionalDocumentReference>
        <cbc:ID>{{ rel.nroDoc }}</cbc:ID>
        <cbc:DocumentTypeCode>{{ rel.tipoDoc }}</cbc:DocumentTypeCode>
    </cac:AdditionalDocumentReference>
    {% endfor -%}
    {% endif -%}
    {% set emp = doc.company -%}
    <cac:Signature>
        <cbc:ID>{{ emp.ruc }}</cbc:ID>
        <cbc:Note>FACTURALO</cbc:Note>
        <cac:SignatoryParty>
            <cac:PartyIdentification>
                <cbc:ID>{{ emp.ruc }}</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name><![CDATA[{{ emp.nombreComercial|raw }}]]></cbc:Name>
            </cac:PartyName>
        </cac:SignatoryParty>
        <cac:DigitalSignatureAttachment>
            <cac:ExternalReference>
                <cbc:URI>#FACTURALO-PERU</cbc:URI>
            </cac:ExternalReference>
        </cac:DigitalSignatureAttachment>
    </cac:Signature>
    <cac:AccountingSupplierParty>
        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeID="6">{{ emp.ruc }}</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name><![CDATA[{{ emp.nombreComercial|raw }}]]></cbc:Name>
            </cac:PartyName>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName><![CDATA[{{ emp.razonSocial|raw }}]]></cbc:RegistrationName>
                {% set addr = emp.address -%}
                <cac:RegistrationAddress>
                    <cbc:ID>{{ addr.ubigueo }}</cbc:ID>
                    <cbc:AddressTypeCode>{{ addr.codLocal }}</cbc:AddressTypeCode>
                    {% if addr.urbanizacion -%}
                        <cbc:CitySubdivisionName>{{ addr.urbanizacion }}</cbc:CitySubdivisionName>
                    {% endif -%}
                    <cbc:CityName>{{ addr.provincia }}</cbc:CityName>
                    <cbc:CountrySubentity>{{ addr.departamento }}</cbc:CountrySubentity>
                    <cbc:District>{{ addr.distrito }}</cbc:District>
                    <cac:AddressLine>
                        <cbc:Line><![CDATA[{{ addr.direccion|raw }}]]></cbc:Line>
                    </cac:AddressLine>
                    <cac:Country>
                        <cbc:IdentificationCode>{{ addr.codigoPais }}</cbc:IdentificationCode>
                    </cac:Country>
                </cac:RegistrationAddress>
            </cac:PartyLegalEntity>
            {% if emp.email or emp.telephone -%}
                <cac:Contact>
                    {% if emp.telephone -%}
                        <cbc:Telephone>{{ emp.telephone }}</cbc:Telephone>
                    {% endif -%}
                    {% if emp.email -%}
                        <cbc:ElectronicMail>{{ emp.email }}</cbc:ElectronicMail>
                    {% endif -%}
                </cac:Contact>
            {% endif -%}
        </cac:Party>
    </cac:AccountingSupplierParty>
    {% set client = doc.client -%}
    <cac:AccountingCustomerParty>
        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeID="{{ client.tipoDoc }}">{{ client.numDoc }}</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName><![CDATA[{{ client.rznSocial|raw }}]]></cbc:RegistrationName>
                {% if client.address -%}
                    {% set addr = client.address -%}
                    <cac:RegistrationAddress>
                        {% if addr.ubigueo -%}
                            <cbc:ID>{{ addr.ubigueo }}</cbc:ID>
                        {% endif -%}
                        <cac:AddressLine>
                            <cbc:Line><![CDATA[{{ addr.direccion|raw }}]]></cbc:Line>
                        </cac:AddressLine>
                        <cac:Country>
                            <cbc:IdentificationCode>{{ addr.codigoPais }}</cbc:IdentificationCode>
                        </cac:Country>
                    </cac:RegistrationAddress>
                {% endif -%}
            </cac:PartyLegalEntity>
            {% if client.email or client.telephone -%}
                <cac:Contact>
                    {% if client.telephone -%}
                        <cbc:Telephone>{{ client.telephone }}</cbc:Telephone>
                    {% endif -%}
                    {% if client.email -%}
                        <cbc:ElectronicMail>{{ client.email }}</cbc:ElectronicMail>
                    {% endif -%}
                </cac:Contact>
            {% endif -%}
        </cac:Party>
    </cac:AccountingCustomerParty>
    <cac:TaxTotal>
        <cbc:TaxAmount currencyID="{{ doc.tipoMoneda }}">{{ doc.totalImpuestos|n_format }}</cbc:TaxAmount>
        {% if doc.mtoISC -%}
            <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="{{ doc.tipoMoneda }}">{{ doc.mtoBaseIsc|n_format }}</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="{{ doc.tipoMoneda }}">{{ doc.mtoISC|n_format }}</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cac:TaxScheme>
                        <cbc:ID>2000</cbc:ID>
                        <cbc:Name>ISC</cbc:Name>
                        <cbc:TaxTypeCode>EXC</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        {% endif -%}
        {% if doc.mtoOperGravadas -%}
            <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="{{ doc.tipoMoneda }}">{{ doc.mtoOperGravadas|n_format }}</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="{{ doc.tipoMoneda }}">{{ doc.mtoIGV|n_format }}</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cac:TaxScheme>
                        <cbc:ID>1000</cbc:ID>
                        <cbc:Name>IGV</cbc:Name>
                        <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        {% endif -%}
        {% if doc.mtoOperInafectas -%}
            <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="{{ doc.tipoMoneda }}">{{ doc.mtoOperInafectas|n_format }}</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="{{ doc.tipoMoneda }}">0</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cac:TaxScheme>
                        <cbc:ID>9998</cbc:ID>
                        <cbc:Name>INA</cbc:Name>
                        <cbc:TaxTypeCode>FRE</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        {% endif -%}
        {% if doc.mtoOperExoneradas -%}
            <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="{{ doc.tipoMoneda }}">{{ doc.mtoOperExoneradas|n_format }}</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="{{ doc.tipoMoneda }}">0</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cac:TaxScheme>
                        <cbc:ID>9997</cbc:ID>
                        <cbc:Name>EXO</cbc:Name>
                        <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        {% endif -%}
        {% if doc.mtoOperGratuitas -%}
            <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="{{ doc.tipoMoneda }}">{{ doc.mtoOperGratuitas|n_format }}</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="{{ doc.tipoMoneda }}">0</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cac:TaxScheme>
                        <cbc:ID>9996</cbc:ID>
                        <cbc:Name>GRA</cbc:Name>
                        <cbc:TaxTypeCode>FRE</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        {% endif -%}
        {% if doc.mtoOperExportacion -%}
            <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="{{ doc.tipoMoneda }}">{{ doc.mtoOperExportacion|n_format }}</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="{{ doc.tipoMoneda }}">0</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cac:TaxScheme>
                        <cbc:ID>9995</cbc:ID>
                        <cbc:Name>EXP</cbc:Name>
                        <cbc:TaxTypeCode>FRE</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        {% endif -%}
        {% if doc.mtoOtrosTributos -%}
            <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="{{ doc.tipoMoneda }}">{{ doc.mtoBaseOth|n_format }}</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="{{ doc.tipoMoneda }}">{{ doc.mtoOtrosTributos|n_format }}</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cac:TaxScheme>
                        <cbc:ID>9999</cbc:ID>
                        <cbc:Name>OTROS</cbc:Name>
                        <cbc:TaxTypeCode>OTH</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        {% endif -%}
    </cac:TaxTotal>
    <cac:RequestedMonetaryTotal>
        {% if doc.sumOtrosCargos -%}
        <cbc:ChargeTotalAmount currencyID="{{ doc.tipoMoneda }}">{{ doc.sumOtrosCargos|n_format }}</cbc:ChargeTotalAmount>
        {% endif -%}
        <cbc:PayableAmount currencyID="{{ doc.tipoMoneda }}">{{ doc.mtoImpVenta|n_format }}</cbc:PayableAmount>
    </cac:RequestedMonetaryTotal>
    {% for detail in doc.details -%}
    <cac:DebitNoteLine>
        <cbc:ID>{{ loop.index }}</cbc:ID>
        <cbc:DebitedQuantity unitCode="{{ detail.unidad }}">{{ detail.cantidad }}</cbc:DebitedQuantity>
        <cbc:LineExtensionAmount currencyID="{{ doc.tipoMoneda }}">{{ detail.mtoValorVenta|n_format }}</cbc:LineExtensionAmount>
        <cac:PricingReference>
            {% if detail.mtoPrecioUnitario -%}
            <cac:AlternativeConditionPrice>
                <cbc:PriceAmount currencyID="{{ doc.tipoMoneda }}">{{ detail.mtoPrecioUnitario|n_format(6) }}</cbc:PriceAmount>
                <cbc:PriceTypeCode>01</cbc:PriceTypeCode>
            </cac:AlternativeConditionPrice>
            {% endif -%}
            {% if detail.mtoValorGratuito -%}
            <cac:AlternativeConditionPrice>
                <cbc:PriceAmount currencyID="{{ doc.tipoMoneda }}">{{  detail.mtoValorGratuito|n_format(6) }}</cbc:PriceAmount>
                <cbc:PriceTypeCode>02</cbc:PriceTypeCode>
            </cac:AlternativeConditionPrice>
            {% endif -%}
        </cac:PricingReference>
        <cac:TaxTotal>
            <cbc:TaxAmount currencyID="{{ doc.tipoMoneda }}">{{ detail.totalImpuestos|n_format }}</cbc:TaxAmount>
            {% if detail.isc -%}
            <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="{{ doc.tipoMoneda }}">{{ detail.mtoBaseIsc|n_format }}</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="{{ doc.tipoMoneda }}">{{ detail.isc|n_format }}</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cbc:Percent>{{ detail.porcentajeIsc }}</cbc:Percent>
                    <cbc:TierRange>{{ detail.tipSisIsc }}</cbc:TierRange>
                    <cac:TaxScheme>
                        <cbc:ID>2000</cbc:ID>
                        <cbc:Name>ISC</cbc:Name>
                        <cbc:TaxTypeCode>EXC</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
            {% endif -%}
            <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="{{ doc.tipoMoneda }}">{{ detail.mtoBaseIgv|n_format }}</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="{{ doc.tipoMoneda }}">{{ detail.igv|n_format }}</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cbc:Percent>{{ detail.porcentajeIgv }}</cbc:Percent>
                    <cbc:TaxExemptionReasonCode>{{ detail.tipAfeIgv }}</cbc:TaxExemptionReasonCode>
                    {% set afect = getTributoAfect(detail.tipAfeIgv) -%}
                    <cac:TaxScheme>
                        <cbc:ID>{{ afect.id }}</cbc:ID>
                        <cbc:Name>{{ afect.name }}</cbc:Name>
                        <cbc:TaxTypeCode>{{ afect.code }}</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
            {% if detail.otroTributo -%}
            <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="{{ doc.tipoMoneda }}">{{ detail.mtoBaseOth|n_format }}</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="{{ doc.tipoMoneda }}">{{ detail.otroTributo|n_format }}</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cbc:Percent>{{ detail.porcentajeOth }}</cbc:Percent>
                    <cac:TaxScheme>
                        <cbc:ID>9999</cbc:ID>
                        <cbc:Name>OTROS</cbc:Name>
                        <cbc:TaxTypeCode>OTH</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
            {% endif -%}
        </cac:TaxTotal>
        <cac:Item>
            <cbc:Description><![CDATA[{{ detail.descripcion|raw }}]]></cbc:Description>
            {% if detail.codProducto -%}
                <cac:SellersItemIdentification>
                    <cbc:ID>{{ detail.codProducto }}</cbc:ID>
                </cac:SellersItemIdentification>
            {% endif -%}
            {% if detail.codProdSunat -%}
                <cac:CommodityClassification>
                    <cbc:ItemClassificationCode>{{ detail.codProdSunat }}</cbc:ItemClassificationCode>
                </cac:CommodityClassification>
            {% endif -%}
            {% if detail.codProdGS1 -%}
                <cac:StandardItemIdentification>
                    <cbc:ID>{{ detail.codProdGS1 }}</cbc:ID>
                </cac:StandardItemIdentification>
            {% endif -%}
            {% if detail.atributos -%}
                {% for atr in detail.atributos -%}
                    <cac:AdditionalItemProperty >
                        <cbc:Name>{{ atr.name }}</cbc:Name>
                        <cbc:NameCode>{{ atr.code }}</cbc:NameCode>
                        {% if atr.value -%}
                            <cbc:Value>{{ atr.value }}</cbc:Value>
                        {% endif -%}
                        {% if atr.fecInicio or atr.fecFin or atr.duracion -%}
                            <cac:UsabilityPeriod>
                                {% if atr.fecInicio -%}
                                    <cbc:StartDate>{{ atr.fecInicio|date('Y-m-d') }}</cbc:StartDate>
                                {% endif -%}
                                {% if atr.fecFin -%}
                                    <cbc:EndDate>{{ atr.fecFin|date('Y-m-d') }}</cbc:EndDate>
                                {% endif -%}
                                {% if atr.duracion -%}
                                    <cbc:DurationMeasure unitCode="DAY">{{ atr.duracion }}</cbc:DurationMeasure>
                                {% endif -%}
                            </cac:UsabilityPeriod>
                        {% endif -%}
                    </cac:AdditionalItemProperty>
                {% endfor -%}
            {% endif -%}
        </cac:Item>
        <cac:Price>
            <cbc:PriceAmount currencyID="{{ doc.tipoMoneda }}">{{ detail.mtoValorUnitario|n_format(6) }}</cbc:PriceAmount>
        </cac:Price>
    </cac:DebitNoteLine>
   {% endfor -%}
</DebitNote>