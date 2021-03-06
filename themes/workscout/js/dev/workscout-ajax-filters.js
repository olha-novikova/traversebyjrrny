jQuery(document).ready(function(a) {
    function e(e, t) {
        if ($supports_html5_history) {
            var r = a(".job_filters"),
                i = a(r).serialize(),
                n = a("div.job_listings").index(e);
            window.history.replaceState({
                id: "job_manager_state",
                page: t,
                data: i,
                index: n
            }, "", s + "#s=1")
        }
    }
    var t = [];
    a(".job_listings").on("update_results", function(e, r, i, s) {
        var n = "",
            o = a(this),
            l = a(".job_filters"),
            _ = "h2.showing_jobs",
            d = o.find(".job_listings"),
            g = o.data("per_page"),
            c = o.data("orderby"),
            u = o.data("order"),
            f = o.data("featured"),
            h = o.data("filled"),
            p = a("div.job_listings").index(this),
            m = a(".listings-loader");
        if (!(p < 0)) {
            t[p] && t[p].abort(), i || (a(d).addClass("loading"), a(m).fadeIn(), r > 1 && 1 != o.data("show_pagination") ? a(d).before('<a class="load_more_jobs load_previous" href="#"><strong>' + job_manager_ajax_filters.i18n_load_prev_listings + "</strong></a>") : o.find(".load_previous").remove(), o.find(".load_more_jobs").data("page", r));
            var b = [];
            a(':input[name="filter_job_type[]"]:checked, :input[name="filter_job_type[]"][type="hidden"], :input[name="filter_job_type"]', l).each(function() {
                b.push(a(this).val())
            });
            var j = l.find(':input[name^="search_categories"]').map(function() {
                    return a(this).val()
                }).get(),
                v = "",
                y = "",
                w = l.find(':input[name="search_keywords"]'),
                x = l.find(':input[name="search_location"]');
            w.val() !== w.attr("placeholder") && (v = w.val()), x.val() !== x.attr("placeholder") && (y = x.val()), n = {
                lang: job_manager_ajax_filters.lang,
                search_keywords: v,
                search_location: y,
                search_categories: j,
                filter_job_type: b,
                per_page: g,
                orderby: c,
                order: u,
                page: r,
                featured: f,
                filled: h,
                show_pagination: o.data("show_pagination"),
                form_data: l.serialize()
            }, t[p] = a.ajax({
                type: "POST",
                url: job_manager_ajax_filters.ajax_url.toString().replace("%%endpoint%%", "get_listings"),
                data: n,
                success: function(e) {
                    if (e) try {
                        e.showing ? (a(_).show().html(e.showing), a(".sidebar .job_filters_links").html(e.showing_links)) : (a(_).hide(), a(".sidebar .job_filters_links").hide()), a("#titlebar .count_jobs").html(e.post_count), e.showing_all ? a(_).addClass("wp-job-manager-showing-all") : a(_).removeClass("wp-job-manager-showing-all"), e.html && (i && s ? a(d).prepend(e.html) : i ? a(d).append(e.html) : a(d).html(e.html)), 1 == o.data("show_pagination") ? (o.find(".job-manager-pagination").remove(), e.pagination && o.append(e.pagination)) : (!e.found_jobs || e.max_num_pages <= r ? a(".load_more_jobs:not(.load_previous)", o).hide() : s || a(".load_more_jobs", o).show(), a(".load_more_jobs i", o).removeClass("fa fa-refresh fa-spin").addClass("fa fa-plus-circle"), a("li.job_listing", d).css("visibility", "visible")), a(d).removeClass("loading"), a(m).fadeOut(), o.triggerHandler("updated_results", e)
                    } catch (a) {
                        window.console && console.log(a)
                    }
                },
                error: function(a, e, t) {
                    window.console && "abort" !== e && console.log(e + ": " + t)
                },
                statusCode: {
                    404: function() {
                        window.console && console.log("Error 404: Ajax Endpoint cannot be reached. Go to Settings > Permalinks and save to resolve.")
                    }
                }
            })
        }
    });
    var r = parseInt(job_manager_ajax_filters.salary_min, 10),
        i = parseInt(job_manager_ajax_filters.salary_max, 10);
    a("#salary-range").slider({
        range: !0,
        min: parseInt(job_manager_ajax_filters.salary_min, 10),
        max: parseInt(job_manager_ajax_filters.salary_max, 10),
        values: [r, i],
        step: 1e3,
        slide: function(e, t) {
            a("input#salary_amount").val(t.values[0] + "-" + t.values[1]), a(".salary_amount .from").text(job_manager_ajax_filters.currency + t.values[0]), a(".salary_amount .to").text(job_manager_ajax_filters.currency + t.values[1])
        }
    }), a(".salary_amount .from").text(job_manager_ajax_filters.currency + a("#salary-range").slider("values", 0)), a(".salary_amount .to").text(job_manager_ajax_filters.currency + a("#salary-range").slider("values", 1)), a("input#salary_amount").val(a("#salary-range").slider("values", 0) + "-" + a("#salary-range").slider("values", 1));
    var r = parseInt(job_manager_ajax_filters.rate_min, 10),
        i = parseInt(job_manager_ajax_filters.rate_max, 10);
    a("#rate-range").slider({
        range: !0,
        min: parseInt(job_manager_ajax_filters.rate_min, 10),
        max: parseInt(job_manager_ajax_filters.rate_max, 10),
        values: [r, i],
        step: 10,
        slide: function(e, t) {
            a("input#rate_amount").val(t.values[0] + "-" + t.values[1]), a(".rate_amount .from").text(job_manager_ajax_filters.currency + t.values[0]), a(".rate_amount .to").text(job_manager_ajax_filters.currency + t.values[1])
        }
    }), a(".rate_amount .from").text(job_manager_ajax_filters.currency + a("#rate-range").slider("values", 0)), a(".rate_amount .to").text(job_manager_ajax_filters.currency + a("#rate-range").slider("values", 1)), a("input#rate_amount").val(a("#rate-range").slider("values", 0) + "-" + a("#rate-range").slider("values", 1)), a("#search_keywords, #search_location, #search_radius, .radius_type, .job_types :input, #search_categories, .job-manager-filter, .filter_by_check").change(function() {
        var t = a("div.job_listings");
        t.triggerHandler("update_results", [1, !1]), e(t, 1)
    }).on("keyup", function(e) {
            13 === e.which && a(this).trigger("change")
        }), a(".filter_by_check").change(function(e) {
        a(this).parents(".widget").find(".widget_range_filter-inside").toggle()
    }), a("#salary-range,#rate-range").on("slidestop", function(t, r) {
        var i = a("div.job_listings");
        i.triggerHandler("update_results", [1, !1]), e(i, 1)
    }), a(".job_filters").on("click", ".reset", function() {
        var t = a("div.job_listings"),
            r = a(this).closest("form");
        return r.find(':input[name="search_keywords"], :input[name="search_location"], .job-manager-filter').not(':input[type="hidden"]').val("").trigger("chosen:updated"), r.find(':input[name^="search_categories"]').not(':input[type="hidden"]').val(0).trigger("chosen:updated"), a(':input[name="filter_job_type[]"]', r).not(':input[type="hidden"]').attr("checked", "checked"), a(".search_keywords #search_keywords").val(""), a("#search_radius").val(""), a(".job_filters #search_keywords").val(""), a(".filter_by_check").prop("checked", !1), a(".widget_range_filter-inside").hide(), a("#salary-range,#rate-range").each(function() {
            var e = a(this).slider("option");
            a(this).slider("values", [e.min, e.max])
        }), t.triggerHandler("reset"), t.triggerHandler("update_results", [1, !1]), e(t, 1), !1
    }), a(document.body).on("click", ".load_more_jobs", function() {
        var t = a(this).closest("div.job_listings"),
            r = parseInt(a(this).data("page") || 1),
            i = !1;
        return a(this).find("i").removeClass("fa fa-plus-circle").addClass("fa fa-refresh fa-spin"), a(this).is(".load_previous") ? (r -= 1, i = !0, 1 === r ? a(this).remove() : a(this).data("page", r)) : (r += 1, a(this).data("page", r), e(t, r)), t.triggerHandler("update_results", [r, !0, i]), !1
    }), a("div.job_listings").on("click", ".job-manager-pagination a", function() {
        var t = a(this).closest("div.job_listings"),
            r = a(this).data("page");
        return e(t, r), t.triggerHandler("update_results", [r, !1]), a("body, html").animate({
            scrollTop: t.offset().top - 40
        }, 600), !1
    }), a.isFunction(a.fn.chosen) && (1 == job_manager_ajax_filters.is_rtl && a('select[name^="search_categories"]').addClass("chosen-rtl"), a('select[name^="search_categories"]').chosen({
        search_contains: !0
    })), window.history && window.history.pushState ? $supports_html5_history = !0 : $supports_html5_history = !1;
    var s = document.location.href.split("#")[0];
    a(window).on("load", function(e) {
        a(".job_filters").each(function() {
            var e = a("div.job_listings"),
                t = a(".job_filters"),
                r = 1,
                i = a("div.job_listings").index(e);
            if (window.history.state && window.location.hash) {
                var s = window.history.state;
                s.id && "job_manager_state" === s.id && i == s.index && (r = s.page, t.deserialize(s.data), t.find(':input[name^="search_categories"]').not(':input[type="hidden"]').trigger("chosen:updated"))
            }
            e.triggerHandler("update_results", [r, !1])
        })
    })
}), jQuery(document).ready(function(a) {
    a(".job_filters").on("click", ".filter_by_tag a", function(e) {
        e.preventDefault();
        var t = a(this).text(),
            r = a(".filter_by_tag").find('input[value="' + t + '"]');
        return r.size() > 0 ? (a(r).remove(), a(this).removeClass("active")) : (a(".filter_by_tag").append('<input type="hidden" name="job_tag[]" value="' + t + '" />'), a(this).addClass("active")), a(".job_listings").trigger("update_results", [1, !1]), !1
    }), a(".job_listings").on("reset", function() {
        a(".filter_by_tag a.active").removeClass("active"), a(".filter_by_tag input").remove()
    }).on("updated_results", function(e, t) {
            if (t.tag_filter) {
                var r = a(".job_filters");
                r.find(".filter_by_tag_cloud").html(t.tag_filter), r.find(".filter_by_tag").show(), r.find(".filter_by_tag input").each(function() {
                    var e = a(this).val();
                    r.find(".filter_by_tag a").each(function() {
                        a(this).text() === e && a(this).addClass("active")
                    })
                })
            } else a(".job_filters").find(".filter_by_tag").hide()
        }).on("change", "#search_categories", function() {
            var e = a(this).closest("div.job_listings");
            a(".job_filters").find(".filter_by_tag input").remove(), e.trigger("update_results", [1, !1])
        })
});