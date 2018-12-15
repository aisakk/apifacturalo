<?php
namespace App\Http\Controllers;

use App\CoreFacturalo\Helpers\StorageDocument;
use App\Http\Requests\DocumentVoidedRequest;
use App\Http\Resources\DocumentCollection;
use App\Models\Company;
use App\Models\Document;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    use StorageDocument;

    public function index()
    {
        return view('documents.index');
    }

    public function columns()
    {
        return [
            'id' => 'Código',
            'number' => 'Número'
        ];
    }

    public function records(Request $request)
    {
        $records = Document::where($request->get('column'), 'like', "%{$request->get('value')}%")
                            ->whereUser()
                            ->orderBy('series')
                            ->orderBy('number', 'desc');

        return new DocumentCollection($records->paginate(env('ITEMS_PER_PAGE', 10)));
    }

//    public function create()
//    {
//        return view('documents.form');
//    }
//
//    public function tables()
//    {
//        $document_types_invoice = Code::byCatalogOnlyCodes('01', ['01', '03']);
//        $document_types_note = Code::byCatalogOnlyCodes('01', ['07', '08']);
//        $note_credit_types = Code::byCatalog('09');
//        $note_debit_types = Code::byCatalog('10');
//        $currency_types = Code::byCatalog('02');
//        $affectation_igv_types = Code::byCatalogOnlyCodes('07', ['10', '20']);
//        $customers = $this->table('customers');
//        $items = $this->table('items');
//        $company = Company::with(['identity_document_type'])->first();
//        $establishment = Establishment::first();
//        $series = Series::all();
//
//        return compact('document_types_invoice', 'document_types_note', 'note_credit_types', 'note_debit_types',
//                       'currency_types', 'customers', 'items', 'company', 'establishment', 'series', 'affectation_igv_types');
//    }
//
//    public function item_tables()
//    {
//        $items = $this->table('items');
//        $affectation_igv_types = Code::byCatalogOnlyCodes('07', ['10', '20']);
//        $unit_types = [];//Code::byCatalog('03');
//        $categories = [];//Category::cascade();
//
//
//        return compact('items', 'unit_types', 'categories', 'affectation_igv_types');
//    }
//
//    public function table($table)
//    {
//        if ($table === 'customers') {
//            $customers = Customer::with(['identity_document_type'])->orderBy('name')->get()->transform(function($row) {
//                return [
//                    'id' => $row->id,
//                    'description' => $row->number.' - '.$row->name,
//                    'name' => $row->name,
//                    'number' => $row->number,
//                    'identity_document_type_id' => $row->identity_document_type_id,
//                    'identity_document_type_code' => $row->identity_document_type->code
//                ];
//            });
//            return $customers;
//        }
//        if ($table === 'items') {
//            return Item::with(['unit_type'])->orderBy('description')->get();
//        }
//
//        return [];
//    }
//
//    public function record($id)
//    {
//        $record = new DocumentResource(Document::with(['customer'])->findOrFail($id));
//
//        return $record;
//    }
//
//    public function store(DocumentRequest $request)
//    {
//        $document = DB::connection('tenant')->transaction(function () use($request) {
//            $document_type_code = ($request->has('document'))?$request->input('document.document_type_code'):
//                                                              $request->input('document_type_code');
//            switch ($document_type_code) {
//                case '01':
//                case '03':
//                    $builder = new InvoiceBuilder();
//                    break;
//                case '07':
//                    $builder = new NoteCreditBuilder();
//                    break;
//                case '08':
//                    $builder = new NoteDebitBuilder();
//                    break;
//                default:
//                    throw new Exception('Tipo de documento ingresado es inválido');
//            }
//
//            $builder->save($request->all());
//            $xmlBuilder = new XmlBuilder();
//            $xmlBuilder->createXMLSigned($builder);
//            $document = $builder->getDocument();
//
//            return $document;
//        });
//
//        return [
//            'success' => true,
//            'data' => [
//                'id' => $document->id,
//                'number' => $document->number_full,
//                'hash' => $document->hash,
//                'qr' => $document->qr,
//                'filename' => $document->filename,
//                'external_id' => $document->external_id,
//                'number_to_letter' => $document->number_to_letter,
//                'link_xml' => $document->download_xml,
//                'link_pdf' => $document->download_pdf,
//                'link_cdr' => $document->download_cdr,
//            ]
//        ];
//    }

    public function downloadExternal($type, $external_id)
    {
        $document = Document::where('external_id', $external_id)->first();

        return $this->download($type, $document);
    }

    public function download($type, Document $document)
    {
        switch ($type) {
            case 'pdf':
                $folder = 'pdf';
                $extension = 'pdf';
                $filename = $document->filename;
                break;
            case 'xml':
                $folder = 'signed';
                $extension = 'xml';
                $filename = $document->filename;
                break;
            case 'cdr':
                $folder = 'cdr';
                $extension = 'zip';
                $filename = 'R-'.$document->filename;
                break;
            default:
                throw new Exception('Tipo de archivo a descargar es inválido');
        }

        $company = Company::byUser();
        return $this->downloadStorage($company->number, $folder, $filename, $extension);
    }

    public function to_print($id)
    {
        $document = Document::find($id);
        $pathToFile = public_path('downloads'.DIRECTORY_SEPARATOR.$document->filename.'.pdf');
        file_put_contents($pathToFile, $this->getStorage('pdf', $document->filename, 'pdf'));

        return response()->file($pathToFile);
    }

    public function voided(DocumentVoidedRequest $request)
    {
        DB::connection('tenant')->transaction(function () use($request) {
            $document = Document::find($request->input('id'));
            $document->state_type_id = '13';
            $document->voided_description = $request->input('voided_description');
            $document->save();

            if ($document->group_id === '01') {
                $builder = new VoidedBuilder();
                $builder->save($document);
                $xmlBuilder = new XmlBuilder();
                $xmlBuilder->createXMLSigned($builder);
            } else {
                $builder = new SummaryBuilder();
                $builder->voided($document);
                $xmlBuilder = new XmlBuilder();
                $xmlBuilder->createXMLSigned($builder);
            }
        });

        return [
            'success' => true,
            'message' => 'Se registró correctamente la anulación, por favor consulte el ticket.'
        ];
    }

//    public function email(DocumentEmailRequest $request)
//    {
//        $company = Company::first();
//        $document = Document::with(['customer'])->find($request->input('id'));
//        $customer_email = $request->input('customer_email');
//
//        Mail::to($customer_email)->send(new DocumentEmail($company, $document));
//
//        return [
//            'success' => true
//        ];
//    }

    public function send_xml($document_id)
    {
        $document = Document::find($document_id);

        $xmlBuilder = new XmlBuilder();
        $res = $xmlBuilder->sendXmlCdr($document);

        return [
            'success' => $res
        ];
    }
}