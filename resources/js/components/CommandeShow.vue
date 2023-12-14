<script>
export default {
    props: ['commande_prop', 'products_prop', 'templates_prop', 'articles_prop', 'commandes_prop'],
    data() {
        return {
            show_products: false,
            selected_product: false,
            selected_template: false,
            selected_element: false,
            reorder_point_id: false,
            dernieres_commandes: false,
            sub_date_apres: false,
            sub_date_avant: false,
            new_section: '',
            isUpdating: false,
            isDeleting: false,
            commande: null,
            commandes: this.commandes_prop,
            nonDispo: {
                commande: null,
                section: null
            },
            isLoading: {
                stock: false,
                reorder_point: false,
                majStock: false,
                article: false
            },
            reorderPoint: null,
            articlesApi: [],
            articlesFetched: [],
            products: null,
            templates: null,
            articles: false,
            section_being_updated: false,
            section_being_deleted: false,
            editing: false,
            vente: false,
            consignment: false,
            sub: false,
            article: false,
            sectionnable_type: false,
            list: false,
            label: '',
            found: false,
        }
    },
    watch: {
        'selected_element': function () {
            document.getElementById('quantiteInput').focus()





            axios.get('/quantite-vendue/' + this.selected_element.id).then(response => {
                this.vente = response.data
                // console.log(response.data);
            }).catch(error => {
                console.log(error);
            });
            axios.get('/consignment/' + this.selected_element.id).then(response => {
                this.consignment = response.data
                // console.log(response.data);
            }).catch(error => {
                console.log(error);
            });

            this.selected_element.sub_loading = true;

            axios.get('/subzero/' + this.selected_element.id + (this.sub_date_apres ? '/' + this.sub_date_apres : '') + (this.sub_date_avant ? '/' + this.sub_date_avant : '')).then(response => {
                this.selected_element.sub = response.data
                // console.log('Sub: ' + response.data)
                this.selected_element.sub_loading = false;
            }).catch(error => {
                console.log(error);
            });

            axios.get('/dernières-commandes/' + this.sectionnable_type + '/' + this.selected_element.id).then(response => {
                console.log(response.data);
                this.dernieres_commandes = response.data
            }).catch(error => {
                console.log(error);
            });

            if (this.sectionnable_type === 'Product') {
                this.selected_element.stock_loading = true;
                axios.get('https://pulldb.stapog.com/api/inventory?product_id=' + this.selected_element.id + '&outlet_id=06bf537b-c77f-11e6-ff13-fb602832ccea').then(response => {
                    this.selected_element.stock = response.data.inventory_level
                    this.selected_element.stock_loading = false;
                    this.$forceUpdate();
                }).catch(error => {
                    this.$swal({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong!',
                        footer: '<a href>Why do I have this issue?</a>'
                    })
                });
            }
        }
    },
    methods: {
        numberOfElementsOrdered(section) {
            var total = 0;
            section.sectionnables.forEach(sectionnable => {
                if (sectionnable.bon_commande.length > 0) {
                    total += 1
                }
            })
            return total;
        },
        asyncFind(query) {
            if (query === '' || this.sectionnable_type !== 'Article') {
                this.articlesApi = []
                return
            }
            this.isLoading.article = true
            console.log(query)
            axios.get('https://stapog.com/article/api/search/' + query).then(response => {
                console.log(response.data);
                this.articlesApi = response.data
            }).catch(error => {
                console.log(error);
            });
        },
        addProduct() {
            console.log(this.selected_product)
            axios.post('/product-commande', { commande_id: this.commande.id, product_id: this.selected_product.id }).then(response => {
                if (response.data === 'OK') {
                    this.commande.products.push(this.selected_product)
                }
            }).catch(error => {
                console.log(error)
                // alert('Un problème est survenu lors du chargement des stocks. Veuillez relancer la MàJ des Stocks')
            });
        },
        addTemplate() {
            axios.post('/template-commande', { commande_id: this.commande.id, template_id: this.selected_template.id }).then(response => {
                if (response.data === 'OK') {
                    this.commande.templates.push(this.selected_template)
                    this.$forceUpdate()
                }
            }).catch(error => {
                console.log(error);
            });
        },
        addSection() {
            if (this.isUpdating === true) {
                this.updateSection(this.section_being_updated)
                this.isUpdating = false
                return 0;
            }
            if (this.new_section) {
                axios.post('/section', { commande: this.commande_prop.id, section: this.new_section }).then(response => {
                    this.commande.sections.push({
                        id: response.data.id,
                        products: {},
                        articles: {},
                        nom: this.new_section
                    });
                    $('#section').modal('hide')
                    // window.location.reload()
                }).catch(error => {
                    console.log(error);
                });
            }
        },
        addProductToSection(section) {
            // Initialise les variables qui serviront a verifier les duplicatas
            var products = []
            var articles = []

            // Grab tous les produits et tous les articles de la commande et store les dans les variables créées pour pouvoir les comparer avec
            this.commande.sections.forEach(sect => {

                if (sect.products && sect.products.length > 0) {
                    sect.products.forEach(prod => {
                        prod.section = sect
                        products.push(prod)
                    })
                }
                //
                if (sect.articles && sect.articles.length > 0) {
                    sect.articles.forEach(art => {
                        art.section = sect
                        articles.push(art)
                    })
                }
            });

            // Check le produit/article selectionné contre tous les produits/articles de la commande
            if (this.sectionnable_type === 'Product') {
                this.found = products.find(prod => {
                    return this.selected_element.id === prod.id
                });
            } else if (this.sectionnable_type === 'Article') {
                this.found = articles.find(art => {
                    return this.selected_element.id === art.id
                });
                // Si on veut ajouter des templates
            } else if (this.sectionnable_type === 'Template') {
                // Initialise variable found tant tableau vide
                this.found = []
                // Pour chaque produit du template
                this.selected_element.products.forEach((temp_prod, index) => {
                    // Comparons a chaque produit dans la commande
                    products.forEach(prod => {
                        // Si Les Deux Match
                        if (temp_prod.id === prod.id) {
                            // Ajoute dans variable found
                            this.found.push(prod)
                            // Retire de la liste des produits
                            this.selected_element.products.splice(index, 1)
                        }
                    })
                })
            }

            // Si le produit existe déjà et qu'il ne s'agit pas de template
            if (this.found && this.sectionnable_type !== 'Template') {
                this.$swal({
                    icon: 'error',
                    title: 'Attention Duplicata',
                    text: 'Ce produit existe déjà dans une section ' + this.found.section.nom
                });
            }
            //
            this.new_section = section
            //
            if (!this.found || (this.found && this.sectionnable_type === 'Template')) {
                axios.post('/product-section', {
                    section: section,
                    product: this.selected_element,
                    type: 'App\\' + this.sectionnable_type
                }
                ).then(response => {
                    // Grab la section
                    var section = this.commande.sections.find((sect, section) => {
                        return sect.id === this.new_section
                    })
                    // Ajoute les produits a la section
                    if (this.sectionnable_type === 'Article') {
                        section.articles.unshift({
                            nom: this.selected_element.nom,
                            pivot: {
                                id: response.data.id,
                                quantite: this.selected_element.quantite
                            },
                        });
                        this.$forceUpdate()
                        axios.get('https://stapog.com/article/api/changer-etat/' + this.selected_element.id + '/wished').then(response => {
                            this.$swal({
                                icon: 'success',
                                title: 'Succès',
                                text: "L'état de votre produit a changé dans Azimuts",
                                position: 'top-end',
                                showClass: {
                                    popup: 'animate__animated animate__fadeInDown'
                                },
                                hideClass: {
                                    popup: 'animate__animated animate__fadeOutUp'
                                },
                                timer: 1500
                            })

                        }).catch(error => {
                            console.log(error);
                        });
                        this.$swal({
                            icon: 'success',
                            title: 'Succès',
                            text: 'Votre Produit a été ajouté avec suuccès',
                            position: 'top-end',
                            showClass: {
                                popup: 'animate__animated animate__fadeInDown'
                            },
                            hideClass: {
                                popup: 'animate__animated animate__fadeOutUp'
                            },
                            timer: 1500
                        })

                    }
                    else if (this.sectionnable_type === 'Template') {
                        this.selected_element.products.forEach(prod => {
                            section.products.unshift(prod)
                        })
                        this.$swal({
                            icon: 'success',
                            title: 'Succès',
                            text: 'Votre template a été ajouté avec suuccès'
                        })

                    } else {
                        section.products.unshift({
                            id: this.selected_element.id,
                            name: this.selected_element.name,
                            pivot: {
                                id: response.data.id,
                                quantite: this.selected_element.quantite
                            },
                        })
                        this.$swal({
                            icon: 'success',
                            title: 'Succès',
                            text: 'Votre Produit a été ajouté avec suuccès',
                            position: 'top-end',
                            showClass: {
                                popup: 'animate__animated animate__fadeInDown'
                            },
                            hideClass: {
                                popup: 'animate__animated animate__fadeOutUp'
                            },
                            timer: 1500
                        })
                    }

                    this.new_section = false
                    document.getElementById('select').focus()
                    document.getElementById('quantiteInput').value = 0
                    // this.found = false
                    // this.selected_element = null
                    this.$forceUpdate()
                }).catch(error => {
                    console.log(error);
                });
            }

            this.found = false
        },
        majStock() {

            if (this.numberOfProducts > 0) {
                // Turn Stock isLoading Flag On
                this.isLoading.stock = true;
                // Grab stock from vend
                axios.get('/api/stock').then(response => {
                    if (this.commande.products) {
                        // If I get response Iterate over Products
                        this.commande.products.forEach(product => {
                            // Foreach Product Iterate over Stock
                            response.data.forEach(stock => {
                                // If Product Matches Stock ...
                                if (product.product_id === stock.product_id) {
                                    // Add Stock to Product
                                    product.stock = stock.inventory_level
                                }
                            });
                        });
                    }

                    if (this.commande.templates) {
                        // Iterate over Templates
                        this.commande.templates.forEach(template => {
                            // Foreach Template Iterate over products
                            template.products.forEach(product => {
                                // Foreach Product Iterate over Stock
                                response.data.forEach(stock => {
                                    // If Product Matches Stock ...
                                    if (product.product_id === stock.product_id) {
                                        // Add Stock to Product
                                        product.stock = stock.inventory_level
                                    }
                                });
                            });
                        });
                    }

                    if (this.commande.reorderpoint[0]) {
                        // For Reorder Point Iterate over products
                        this.commande.reorderpoint[0].products.forEach(product => {
                            // Foreach Product Iterate over Stock
                            response.data.forEach(stock => {
                                // If Product Matches Stock ...
                                if (product.product_id === stock.product_id) {
                                    // Add Stock to Product
                                    product.stock = stock.inventory_level
                                }
                            });
                        });
                    }

                    this.$forceUpdate();
                    this.isLoading.stock = false;
                }).catch(error => {
                    console.log(error);
                });
            } else {
                alert('Aucun Produit dans la commande. Ajoutez des produits')
            }

        },
        // addReorderpoint(){
        //     axios.post('/reorderpoint-commande', {commande_id : this.commande.id}).then(response => {
        //         this.$swal()
        //         console.log(response.data);
        //     }).catch(error => {
        //         console.log(error);
        //     });
        // },
        // Toggle Editing
        toggleEdit() {
            this.editing = !this.editing
        },
        // Enregistre les quantités souhaitées
        save() {
            axios.post('/commande-quantité', this.commande).then(response => {
                console.log(response.data);
            }).catch(error => {
                console.log(error);
            });
        },
        mapArrays() {
            if (this.commande && this.commande.templates[0] && this.commande.templates[0].products) {
                this.commande.templates[0].products.map(template_product => {
                    var found = this.commande.products.find(product => {
                        return product.id === template_product.id
                    })
                    template_product.quantity = found.pivot.quantity
                })
            }
        },
        saveQuantity(section, article) {
            this.$forceUpdate()
            if (article.quantite !== null || article.quantite !== '') {
                article.message = 'Sauvegarde en Cours...'
                article.color = 'tw-text-green-500'
                this.$forceUpdate()
                axios.put('/article-update', { section: section, article: article }).then(response => {
                    console.log(response.data);
                    article.message = 'Sauvegarde Réussie.'
                    article.color = 'tw-text-green-500'
                    this.$forceUpdate()
                }).catch(error => {
                    console.log(error);
                    article.message = 'Sauvegarde Échouée. Veuillez vérifier votre connexion Internet ou la Quantité Entrée'
                    article.color = 'tw-text-red-500'
                    this.$forceUpdate()
                });
            }

        },
        openEditModal(section) {
            this.isUpdating = true
            this.section_being_updated = section
            $('#section').modal('show')
            this.new_section = section.nom

        },
        openDeleteModal(section) {
            this.isDeleting = true
            this.section_being_deleted = section
            $('#sectionDelete').modal('show')
        },
        updateSection(section) {
            axios.put('/section/' + this.section_being_updated.id, { nom: this.new_section }).then(response => {
                console.log(response.data);
                this.section_being_updated.nom = this.new_section
                this.isUpdating = false
                this.section_being_updated = false
                this.new_section = false
                this.$forceUpdate()
                $('#section').modal('hide')
            }).catch(error => {
                console.log(error);
            });
        },
        removeSection(section) {
            axios.delete('/section/' + this.section_being_deleted.id).then(response => {

                var index = this.commande.sections.indexOf(section)
                this.commande.sections.splice(index, 1)
                $('#sectionDelete').modal('hide')
                this.$forceUpdate()

                console.log(response.data);
            }).catch(error => {
                console.log(error);
            });
        },
        removeProduct(section, produit, type) {
            this.$swal(
                {
                    title: 'Êtes-vous sûr de vouloir supprimer cette ressource?',
                    text: "Cette action est irréversible!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Oui, Supprimer!',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.value) {
                        axios.delete('/sectionnable/' + produit.sectionnable_id + '/' + section.id)
                            .then(response => {
                                console.log(response.data)
                                if (type === 'Product') {
                                    var index = section.sectionnables.indexOf(produit)
                                    section.sectionnables.splice(index, 1)
                                    this.$swal({
                                        icon: 'success',
                                        title: 'Succès',
                                        text: 'Le produit a été supprimé ',
                                        position: 'top-end',
                                        showClass: {
                                            popup: 'animate__animated animate__fadeInDown'
                                        },
                                        hideClass: {
                                            popup: 'animate__animated animate__fadeOutUp'
                                        },
                                        timer: 1500
                                    })
                                } else {
                                    axios.get('https://stapog.com/article/api/changer-etat/' + produit.pivot.id + '/enregistré').then(response => {
                                        console.log(response.data);
                                        var index = section.articles.indexOf(produit)
                                        section.articles.splice(index, 1)
                                        this.$forceUpdate()
                                        this.$swal({
                                            icon: 'success',
                                            title: 'Succès',
                                            text: 'Le produit a été supprimé ',
                                            position: 'top-end',
                                            showClass: {
                                                popup: 'animate__animated animate__fadeInDown'
                                            },
                                            hideClass: {
                                                popup: 'animate__animated animate__fadeOutUp'
                                            },
                                            timer: 1500
                                        })
                                    }).catch(error => {
                                        console.log(error);
                                    });

                                }

                                this.$forceUpdate()

                            }).catch(error => {
                                console.log(error);
                            });
                    }
                }
                )

        },
        majStock() {
            this.isLoading.majStock = true;
            axios.get('/vend/update-quantities').then(response => {
                console.log(response.data);
                this.isLoading.majStock = false
            }).catch(error => {
                console.log(error);
            });
        },
        addReorderPoint() {
            axios.get('/api/vend/commande/' + this.commande.id + '/reorderpoint/' + this.reorder_point_id).then(response => {
                console.log(response.data)
                $('#reorderpoint').modal('hide')
                if (response.data.inserted === 0 || response.data.inserted < response.data.products) {
                    this.$swal({
                        icon: 'error',
                        title: response.data.inserted + '/' + response.data.products + ' Produits Enregistrés.',
                        text: 'Il existe ' + (response.data.products - response.data.inserted) + ' Produits déjà enregistrés. Aucun Duplicata n est accepté',
                    })
                } else {
                    this.$swal({
                        icon: 'success',
                        title: response.data.inserted + '/' + response.data.products + ' Produits Enregistrés'
                    })
                }
            }).catch(error => {
                this.$swal({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Une erreur est survenue veuillez vous assurer ',
                    footer: '<a href>Why do I have this issue?</a>'
                })
            });
        },
        ajouterNonDispo() {
            axios.post('/ajouter-non-dispo', this.nonDispo)
                .then(response => {
                    console.log(response.data)
                }).catch(error => {
                    console.log(error)
                })
        },

    },
    computed: {
        totalNumberOfProductsOrdered() {
        },
        numberOfProducts() {
            var total = 0;
            if (this.commande) {

                if (this.commande.templates) {
                    this.commande.templates.forEach(template => {
                        if (template.products) {
                            total += template.products.length;
                        } else {
                            total += 0;
                        }
                    });
                }

                if (this.commande.reorderpoint) {
                    this.commande.reorderpoint.forEach(reorderpoint => {
                        total += reorderpoint.products.length;
                    });
                }

                if (this.commande.products) {
                    total += this.commande.products.length;
                }

                if (this.commande.sections) {
                    this.commande.sections.forEach(section => {
                        if (section.articles && section.products) {
                            // if( section.articles.length > 0 || section.products.length > 0 ){
                            //     // total += section.articles.length + section.products.length
                            // }
                        }
                    })
                }

            }
            return total;
        },
        numberOfNewProducts() {
            var total = 0;
            if (this.commande.sections) {
                this.commande.sections.forEach(section => {
                    if (section.articles) {
                        if (section.articles.length > 0) {
                            total += section.articles.length
                        }
                    }
                })
            }
            return total
        },
        numberOfVendProducts() {
            var total = 0;
            if (this.commande.sections) {
                this.commande.sections.forEach(section => {
                    if (section.products) {
                        if (section.products.length > 0) {
                            total += section.products.length
                        }
                    }
                })
            }
            return total
        },
        prixMoyenDemande() {
            var total = 0;
            if (this.commande.demandes.length > 1) {
                total = this.commande.demandes.reduce((a, b) => {

                    if (a.sectionnables && a.sectionnables.length > 0) {
                        a.total = a.sectionnables.reduce((x, y) => {
                            if (x && y && x.sectionnable_type === 'App\Product' && y.sectionnable_type === 'App\Product') {

                                // return (x.pivot.quantite_offerte * x.pivot.offre) + (y.pivot.quantite_offerte * y.pivot.offre)
                            }
                        })
                    }

                    if (b.sectionnables && b.sectionnables.length > 0) {
                        b.total = b.sectionnables.reduce((x, y) => {
                            if (x && y && x.sectionnable_type === 'App\Product' && y.sectionnable_type === 'App\Product') {
                                // return (x.pivot.quantite_offerte * x.pivot.offre) + (y.pivot.quantite_offerte * y.pivot.offre)
                            }
                            // return (x.pivot.quantite_offerte * x.pivot.offre) + (y.pivot.quantite_offerte * y.pivot.offre)
                        })
                    }

                    return (a.total + b.total)
                })

            } else if (this.commande.demandes.length === 1 && this.commande.demandes[0].sectionnables.length > 0) {
                total = this.commande.demandes[0].sectionnables.reduce((x, y) => {
                    // return (x.pivot.quantite_offerte * x.pivot.offre) + (y.pivot.quantite_offerte * y.pivot.offre)
                })
            } else {
                return '*********'
            }
            var prix_moyen = 0
            if (this.commande.demandes.length > 0)
                return prix_moyen = total / (this.commande.demandes.length)
        },
        list_type() {
            if (this.sectionnable_type === 'Product') {
                this.label = 'variant_name'
                return this.products
            } else if (this.sectionnable_type === 'Template') {
                this.label = 'name'
                return this.templates
            }
            else if (this.sectionnable_type === 'Article') {
                this.label = 'nom'
                return this.articlesApi
            } else {
                return this.products
            }
        },

    },
    created() {

        this.sectionnable_type = 'Product'

        if (this.commande_prop) {
            this.commande = this.commande_prop
        }



        if (this.products_prop) {
            this.products = this.products_prop

            this.products.map(product => {
                product.message = {
                    text: '',
                    color: ''
                }
            })
        }

        if (this.templates_prop) {
            this.templates = this.templates_prop
        }
        this.commande.sections.forEach(section => {
            section.articles = []
        })

        // console.log(this.articles_prop)
        var article_ids = []

        this.articles_prop.forEach(article => {
            article_ids.push(article.sectionnable_id)
        });

        axios.post('https://stapog.com/article/api/bulk-fetch', article_ids).then(response => {
            console.log(response.data)
            this.articlesFetched = response.data;
            this.articlesFetched.forEach(artFetched => {
                this.articles_prop.forEach(artProp => {
                    if (artFetched.id == artProp.sectionnable_id) {
                        artFetched.pivot = {
                            section_id: artProp.section_id,
                            quantite: artProp.quantite,
                            id: +artProp.sectionnable_id
                        }
                    }
                })
            });
            this.articlesFetched.forEach((article, index) => {
                article.message = {
                    text: '',
                    color: ''
                }
                this.commande.sections.forEach(section => {
                    if (section.id === article.pivot.section_id) {
                        section.articles.push(article)
                    }
                })
            })
            this.$forceUpdate()
        }).catch(error => {
            console.log(error);
        });
        this.mapArrays()

        // window.Echo.channel('section').listen('SectionAdded', e => {
        //     console.log(e)
        //     this.commande.sections.push({
        //         id: e.section.id,
        //         products: {},
        //         articles: {},
        //         nom: e.section.nom
        //     });
        //     console.log('Section was added');
        //     console.log(e)
        // })
    }
}
</script>
