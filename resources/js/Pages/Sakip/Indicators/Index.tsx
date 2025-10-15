import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { 
  Table, 
  TableBody, 
  TableCell, 
  TableHead, 
  TableHeader, 
  TableRow 
} from '@/components/ui/table';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  Pagination,
  PaginationContent,
  PaginationEllipsis,
  PaginationItem,
  PaginationLink,
  PaginationNext,
  PaginationPrevious,
} from '@/components/ui/pagination';
import { 
  Plus, 
  Search, 
  Filter, 
  Eye, 
  Edit, 
  Trash2, 
  BarChart3,
  Target,
  Calendar,
  AlertCircle
} from 'lucide-react';

export default function PerformanceIndicatorsIndex({ 
  indicators, 
  instansis, 
  categories, 
  filters 
}) {
  const [searchTerm, setSearchTerm] = useState(filters.search || '');
  const [selectedCategory, setSelectedCategory] = useState(filters.category || '');
  const [selectedInstansi, setSelectedInstansi] = useState(filters.instansi_id || '');

  const handleSearch = (value) => {
    setSearchTerm(value);
    router.get(route('sakip.indicators.index'), {
      search: value,
      category: selectedCategory,
      instansi_id: selectedInstansi,
    }, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  const handleFilterChange = (type, value) => {
    if (type === 'category') setSelectedCategory(value);
    if (type === 'instansi') setSelectedInstansi(value);
    
    router.get(route('sakip.indicators.index'), {
      search: searchTerm,
      category: type === 'category' ? value : selectedCategory,
      instansi_id: type === 'instansi' ? value : selectedInstansi,
    }, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  const getFrequencyColor = (frequency) => {
    const colors = {
      monthly: 'bg-blue-100 text-blue-800',
      quarterly: 'bg-green-100 text-green-800',
      semester: 'bg-yellow-100 text-yellow-800',
      annual: 'bg-purple-100 text-purple-800',
    };
    return colors[frequency] || 'bg-gray-100 text-gray-800';
  };

  const getCategoryColor = (category) => {
    const colors = {
      financial: 'bg-emerald-100 text-emerald-800',
      service: 'bg-blue-100 text-blue-800',
      internal: 'bg-orange-100 text-orange-800',
      learning: 'bg-purple-100 text-purple-800',
      stakeholder: 'bg-pink-100 text-pink-800',
      compliance: 'bg-red-100 text-red-800',
      strategic: 'bg-indigo-100 text-indigo-800',
    };
    return colors[category] || 'bg-gray-100 text-gray-800';
  };

  const getStatusColor = (percentage) => {
    if (percentage >= 90) return 'text-green-600';
    if (percentage >= 70) return 'text-yellow-600';
    return 'text-red-600';
  };

  return (
    <>
      <Head title="Performance Indicators" />
      
      <div className="space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Performance Indicators</h1>
            <p className="text-muted-foreground">
              Manage and track performance indicators across all categories
            </p>
          </div>
          <Link href={route('sakip.indicators.create')}>
            <Button>
              <Plus className="h-4 w-4 mr-2" />
              Add Indicator
            </Button>
          </Link>
        </div>

        {/* Filters */}
        <Card>
          <CardContent className="pt-6">
            <div className="flex flex-col gap-4 md:flex-row">
              <div className="relative flex-1">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input
                  placeholder="Search indicators..."
                  value={searchTerm}
                  onChange={(e) => handleSearch(e.target.value)}
                  className="pl-10"
                />
              </div>
              <Select value={selectedCategory} onValueChange={(value) => handleFilterChange('category', value)}>
                <SelectTrigger className="w-full md:w-[200px]">
                  <SelectValue placeholder="All Categories" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="">All Categories</SelectItem>
                  {categories.map((category) => (
                    <SelectItem key={category} value={category}>
                      {category.charAt(0).toUpperCase() + category.slice(1)}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
              <Select value={selectedInstansi} onValueChange={(value) => handleFilterChange('instansi', value)}>
                <SelectTrigger className="w-full md:w-[200px]">
                  <SelectValue placeholder="All Instansi" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="">All Instansi</SelectItem>
                  {Object.entries(instansis).map(([id, name]) => (
                    <SelectItem key={id} value={id}>
                      {name}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
          </CardContent>
        </Card>

        {/* Indicators Table */}
        <Card>
          <CardHeader>
            <CardTitle>Performance Indicators</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Code</TableHead>
                    <TableHead>Name</TableHead>
                    <TableHead>Category</TableHead>
                    <TableHead>Instansi</TableHead>
                    <TableHead>Frequency</TableHead>
                    <TableHead>Weight</TableHead>
                    <TableHead>Mandatory</TableHead>
                    <TableHead>Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {indicators.data.length === 0 ? (
                    <TableRow>
                      <TableCell colSpan={8} className="text-center py-8 text-muted-foreground">
                        No performance indicators found
                      </TableCell>
                    </TableRow>
                  ) : (
                    indicators.data.map((indicator) => (
                      <TableRow key={indicator.id}>
                        <TableCell className="font-medium">{indicator.code}</TableCell>
                        <TableCell>
                          <div className="space-y-1">
                            <p className="font-medium">{indicator.name}</p>
                            <p className="text-sm text-muted-foreground line-clamp-2">
                              {indicator.description}
                            </p>
                          </div>
                        </TableCell>
                        <TableCell>
                          <Badge className={getCategoryColor(indicator.category)}>
                            {indicator.category}
                          </Badge>
                        </TableCell>
                        <TableCell>{indicator.instansi?.name || '-'}</TableCell>
                        <TableCell>
                          <Badge className={getFrequencyColor(indicator.frequency)}>
                            {indicator.frequency}
                          </Badge>
                        </TableCell>
                        <TableCell>
                          <div className="flex items-center gap-2">
                            <span className="font-medium">{indicator.weight}%</span>
                          </div>
                        </TableCell>
                        <TableCell>
                          {indicator.is_mandatory ? (
                            <Badge variant="destructive">Yes</Badge>
                          ) : (
                            <Badge variant="secondary">No</Badge>
                          )}
                        </TableCell>
                        <TableCell>
                          <div className="flex items-center gap-2">
                            <Link href={route('sakip.indicators.show', indicator.id)}>
                              <Button variant="ghost" size="sm">
                                <Eye className="h-4 w-4" />
                              </Button>
                            </Link>
                            <Link href={route('sakip.indicators.edit', indicator.id)}>
                              <Button variant="ghost" size="sm">
                                <Edit className="h-4 w-4" />
                              </Button>
                            </Link>
                            <Button
                              variant="ghost"
                              size="sm"
                              onClick={() => {
                                if (confirm('Are you sure you want to delete this indicator?')) {
                                  router.delete(route('sakip.indicators.destroy', indicator.id));
                                }
                              }}
                            >
                              <Trash2 className="h-4 w-4" />
                            </Button>
                          </div>
                        </TableCell>
                      </TableRow>
                    ))
                  )}
                </TableBody>
              </Table>
            </div>
          </CardContent>
        </Card>

        {/* Pagination */}
        {indicators.links.length > 3 && (
          <Pagination>
            <PaginationContent>
              <PaginationItem>
                <PaginationPrevious 
                  href={indicators.prev_page_url}
                  className={!indicators.prev_page_url ? 'pointer-events-none opacity-50' : ''}
                />
              </PaginationItem>
              
              {indicators.links.map((link, index) => {
                if (index === 0 || index === indicators.links.length - 1) return null;
                
                if (link.label === '...') {
                  return (
                    <PaginationItem key={index}>
                      <PaginationEllipsis />
                    </PaginationItem>
                  );
                }
                
                return (
                  <PaginationItem key={index}>
                    <PaginationLink
                      href={link.url}
                      isActive={link.active}
                    >
                      {link.label}
                    </PaginationLink>
                  </PaginationItem>
                );
              })}
              
              <PaginationItem>
                <PaginationNext
                  href={indicators.next_page_url}
                  className={!indicators.next_page_url ? 'pointer-events-none opacity-50' : ''}
                />
              </PaginationItem>
            </PaginationContent>
          </Pagination>
        )}
      </div>
    </>
  );
}