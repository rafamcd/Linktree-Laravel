<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use App\Models\User;
use App\Models\Page;
use App\Models\Link;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', [
            'except' => ['login', 'loginAction', 'register', 'registerAction']
        ]);
    }    
    
    public function login(Request $request) {
        return view('admin.login', [
            'error' => $request->session()->get('error')
        ]);
    }

    public function loginAction(Request $request) {
        
        $creds = $request->only('email', 'password');
        //tentar realizar o login
        if(Auth::attempt($creds)) {
            return redirect('/admin');
        } else {
            $request->session()->flash('error', 'E-mail e/ou senha não conferem.');
            return redirect('/admin/login');
        }
    }

    public function register(Request $request) {
        return view('admin.register', [
            'error' => $request->session()->get('error')
        ]);
    }

    public function registerAction(Request $request) {
        $creds = $request->only('email', 'password');

        $hasEmail = User::where('email', $creds['email'])->count();
        if($hasEmail === 0) {

            $newUser = new User();
            $newUser->email = $creds['email'];
            $newUser->password = password_hash($creds['password'], PASSWORD_DEFAULT);
            $newUser->save();

            Auth::login($newUser);
            return redirect('/admin');

        } else {
            $request->session()->flash('error', 'Já existe um usuário com este e-mail.');
            return redirect('/admin/register');
        }
    }

    public function logout() {
        Auth::logout();
        return redirect('/admin');
    }

    public function index() {
        $user = Auth::user(); //pega o usuário logado
        $pages = Page::where('id_user', $user->id)->get(); //pegando todas as páginas do usuário logado

        return view('admin.index', [
            'pages' => $pages
        ]);
    }

    public function pageLinks($slug) {
        $user = Auth::user();
        $page = Page::where('slug', $slug)
                ->where('id_user', $user->id)
                ->first();
        
        if($page) {
            $links = Link::where('id_page', $page->id)->orderBy('order', 'ASC')->get();

            return view('admin/page_links', [
                'menu' => 'links',
                'page' => $page,
                'links' => $links
            ]);
        } else {
            return redirect('/admin');
        }
    }

    public function linkOrderUpdate ($linkid, $pos) {
        $user = Auth::user();

        //verificar se o link pertence ao usuário logado, se sim, fazer a ordenação no banco
        $link = Link::find($linkid);
        $myPages = [];
        $myPagesQuery = Page::where('id_user', $user->id)->get();
        foreach($myPagesQuery as $pageItem) {
            $myPages[] = $pageItem->id;
        }

        //agora tenho em myPages um array com os ids das paginas que o usuário logado possui
        //fazer verificação se o link passado como parametro está dentro da minha lista de links
        if(in_array($link->id_page, $myPages)) {

            //agora sim posso fazer a ordenação 
            if($link->order > $pos) {
                //posicionando os links seguintes
                $afterLinks = Link::where('id_page', $link->id_page)
                                    ->where('order', '>=', $pos)
                                    ->get();
                foreach($afterLinks as $afterLink) {
                    $afterLink->order++;
                    $afterLink->save();
                }
            } elseif($link->order < $pos) {
                //posicionando os anteriores para cima   
                $beforeLinks = Link::where('id_page', $link->id_page)
                                    ->where('order', '<=', $pos)
                                    ->get();
                foreach($beforeLinks as $beforeLink) {
                    $beforeLink->order--;
                    $beforeLink->save();
                }
            }

            //posicionando quem eu realmente movimentei
            $link->order = $pos;
            $link->save();

            //corrigindo as posições finais
            $allLinks = Link::where('id_page', $link->id_page)
                            ->orderBy('order', 'ASC')
                            ->get();
            foreach($allLinks as $linkKey => $linkItem) {
                $linkItem->order = $linkKey;     
                $linkItem->save();   
            }
        }        
        return [];
    }

    public function newLink($slug) {
        $user = Auth::user();
        $page = Page::where('id_user', $user->id)
                    ->where('slug', $slug)
                    ->first();
        if($page) {
            return view('admin.page_editlink', [
                'menu' => 'links',
                'page' => $page
            ]);
        } else {
            return redirect('/admin');
        }
    }

    public function newLinkAction(Request $request, $slug) {
        
        $user = Auth::user();
        $page = Page::where('id_user', $user->id)
                    ->where('slug', $slug)
                    ->first();
        if($page) {
            
            /*$fields = $request->validate([
                'status' => ['required', 'boolean'],
                'title' => ['required', 'min:2'],
                'href' => ['required', 'url'],
                'op_bg_color' => ['required', 'regex:/^[#][0-9A-F]{3,6}$/i'],
                'op_text_color' => ['required', 'regex:/^[#][0-9A-F]{3,6}$/i'],
                'op_border_type' => ['required', Rule::in(['square', 'rounded'])]
            ]);*/
            
            $totalLinks = Link::where('id_page', $page->id)->count();
            
            $newLink = new Link();
            $newLink->id_page = $page->id;
            $newLink->status = $request->input('status');
            $newLink->order = $totalLinks;
            $newLink->title = $request->input('title');
            $newLink->href = $request->input('href');
            $newLink->op_bg_color = $request->input('op_bg_color');
            $newLink->op_text_color = $request->input('op_text_color');
            $newLink->op_border_type = $request->input('op_border_type');
            $newLink->save();

            return redirect('/admin/'.$page->slug.'/links');

        } else {
            return redirect('/admin');
        }
    }

    public function editLink($slug, $linkid) {
        $user = Auth::user();
        $page = Page::where('id_user', $user->id)
                    ->where('slug', $slug)
                    ->first();
        if($page) {
            $link = Link::where('id_page', $page->id)
                        ->where('id', $linkid)
                        ->first();
            if($link) {
                return view('admin.page_editlink', [
                    'menu' => 'links',
                    'page' => $page,
                    'link' => $link
                ]);
            }
        }

        return redirect('/admin'); 
    }

    public function editLinkAction(Request $request, $slug, $id) {
        
        $user = Auth::user();
        $page = Page::where('id_user', $user->id)
                    ->where('slug', $slug)
                    ->first();
        if($page) {
            
            /*$fields = $request->validate([
                'status' => ['required', 'boolean'],
                'title' => ['required', 'min:2'],
                'href' => ['required', 'url'],
                'op_bg_color' => ['required', 'regex:/^[#][0-9A-F]{3,6}$/i'],
                'op_text_color' => ['required', 'regex:/^[#][0-9A-F]{3,6}$/i'],
                'op_border_type' => ['required', Rule::in(['square', 'rounded'])]
            ]);*/
            
            $newLink = Link::find($id);
            if($newLink) {
                $newLink->status = $request->input('status');
                $newLink->title = $request->input('title');
                $newLink->href = $request->input('href');
                $newLink->op_bg_color = $request->input('op_bg_color');
                $newLink->op_text_color = $request->input('op_text_color');
                $newLink->op_border_type = $request->input('op_border_type');
                $newLink->save();
                return redirect('/admin/'.$page->slug.'/links');
            }          
            

        } else {
            return redirect('/admin');
        }
    }

    public function delLink($slug, $linkid) {
        $user = Auth::user();
        $page = Page::where('id_user', $user->id)
                    ->where('slug', $slug)
                    ->first();
        if($page) {
            $link = Link::where('id_page', $page->id)
                        ->where('id', $linkid)
                        ->first();
            if($link) {
               $link->delete(); 

            //corrigindo as posições finais
            $allLinks = Link::where('id_page', $page->id)
                    ->orderBy('order', 'ASC')
                    ->get();

            foreach($allLinks as $linkKey => $linkItem) {
                
                $linkItem->order = $linkKey;     
                $linkItem->save();   

            }

               return redirect('/admin/'.$page->slug.'/links');
            }
        }

        return redirect('/admin');
    }

    public function pageDesign($slug) {
        return view('admin/page_design', [
            'menu' => 'design'
        ]);
    }

    public function pageStats($slug) {
        return view('admin/page_stats', [
            'menu' => 'stats'
        ]);
    }

}
