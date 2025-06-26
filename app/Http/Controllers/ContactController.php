<?php
namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only('search');
        
        $contacts = Contact::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString() // Mantém a query string na paginação
            ->through(fn ($contact) => [
                'id' => $contact->id,
                'name' => $contact->name,
                'email' => $contact->email,
                'phone' => $contact->phone,
            ]);

        return Inertia::render('Contacts/Index', [
            'contacts' => $contacts,
            'filters' => $filters, // Envia o termo de busca de volta para a view
        ]);
    }

    public function create()
    {
        return Inertia::render('Contacts/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:contacts',
            'phone' => 'nullable|string|max:255',
        ]);

        $data = $request->all();
        if (!empty($data['phone'])) {
            // Remove caracteres não numéricos e adiciona o prefixo 55
            $data['phone'] = '55' . preg_replace('/[^0-9]/', '', $data['phone']);
        }

        Contact::create($data);

        return Redirect::route('contacts.index')->with('success', 'Contato criado com sucesso.');
    }

    public function edit(Contact $contact)
    {
        return Inertia::render('Contacts/Edit', [
            'contact' => [
                'id' => $contact->id,
                'name' => $contact->name,
                'email' => $contact->email,
                'phone' => $contact->phone,
            ]
        ]);
    }

    public function update(Request $request, Contact $contact)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('contacts')->ignore($contact->id),
            ],
            'phone' => 'nullable|string|max:255',
        ]);

        $data = $request->all();

        if (!empty($data['phone'])) {
            // Remove caracteres não numéricos e adiciona o prefixo 55
            $data['phone'] = '55' . preg_replace('/[^0-9]/', '', $data['phone']);
        } else {
            $data['phone'] = null; // Garante que o campo fique nulo se estiver vazio
        }

        $contact->update($data);

        return Redirect::route('contacts.index')->with('success', 'Contato atualizado com sucesso.');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return Redirect::route('contacts.index')->with('success', 'Contato excluído com sucesso.');
    }
}